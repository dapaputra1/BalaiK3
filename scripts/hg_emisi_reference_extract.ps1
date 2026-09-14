param(
    [Parameter(Mandatory = $true)]
    [string] $Path
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

Add-Type -AssemblyName System.IO.Compression.FileSystem

function Read-ZipEntryText {
    param(
        [Parameter(Mandatory = $true)] [System.IO.Compression.ZipArchive] $Zip,
        [Parameter(Mandatory = $true)] [string] $EntryName
    )

    $entry = $Zip.Entries | Where-Object { $_.FullName -eq $EntryName } | Select-Object -First 1
    if (-not $entry) {
        throw "Entry ZIP '$EntryName' tidak ditemukan."
    }

    $reader = [System.IO.StreamReader]::new($entry.Open())
    try {
        return $reader.ReadToEnd()
    } finally {
        $reader.Dispose()
    }
}

function Read-SharedStrings {
    param([System.IO.Compression.ZipArchive] $Zip)

    try {
        [xml] $sharedXml = Read-ZipEntryText -Zip $Zip -EntryName 'xl/sharedStrings.xml'
    } catch {
        return @()
    }

    $strings = New-Object System.Collections.Generic.List[string]
    foreach ($si in $sharedXml.sst.si) {
        $plainTextNode = $si.SelectSingleNode('./*[local-name()="t"]')
        if ($plainTextNode) {
            [void] $strings.Add([string] $plainTextNode.InnerText)
            continue
        }

        $runNodes = $si.SelectNodes('./*[local-name()="r"]')
        if ($runNodes.Count -gt 0) {
            $text = ''
            foreach ($run in $runNodes) {
                $textNode = $run.SelectSingleNode('./*[local-name()="t"]')
                if ($textNode) {
                    $text += [string] $textNode.InnerText
                }
            }
            [void] $strings.Add($text)
            continue
        }

        [void] $strings.Add('')
    }

    return ,$strings.ToArray()
}

function Resolve-Worksheet {
    param([System.IO.Compression.ZipArchive] $Zip)

    [xml] $workbook = Read-ZipEntryText -Zip $Zip -EntryName 'xl/workbook.xml'
    [xml] $rels = Read-ZipEntryText -Zip $Zip -EntryName 'xl/_rels/workbook.xml.rels'

    $nsWorkbook = [System.Xml.XmlNamespaceManager]::new($workbook.NameTable)
    $nsWorkbook.AddNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main')
    $nsWorkbook.AddNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships')

    $nsRels = [System.Xml.XmlNamespaceManager]::new($rels.NameTable)
    $nsRels.AddNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships')

    $targets = @{}
    foreach ($rel in $rels.SelectNodes('//rel:Relationship', $nsRels)) {
        $targets[$rel.Id] = 'xl/' + $rel.Target.TrimStart('/')
    }

    $sheets = $workbook.SelectNodes('//a:sheets/a:sheet', $nsWorkbook)
    foreach ($sheet in $sheets) {
        $name = [string] $sheet.name
        $relationId = [string] $sheet.GetAttribute('id', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships')
        if (-not $relationId -or -not $targets.ContainsKey($relationId)) {
            continue
        }

        if ($name -match 'hg|merkuri|mercury') {
            return @{
                sheetName = $name
                sheetPath = $targets[$relationId]
            }
        }
    }

    foreach ($sheet in $sheets) {
        $name = [string] $sheet.name
        $relationId = [string] $sheet.GetAttribute('id', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships')
        if ($relationId -and $targets.ContainsKey($relationId)) {
            return @{
                sheetName = $name
                sheetPath = $targets[$relationId]
            }
        }
    }

    throw 'Sheet acuan Hg Emisi tidak ditemukan.'
}

function Read-WorksheetCells {
    param(
        [System.IO.Compression.ZipArchive] $Zip,
        [string] $SheetPath,
        [string[]] $SharedStrings
    )

    [xml] $sheetXml = Read-ZipEntryText -Zip $Zip -EntryName $SheetPath
    $cells = @{}

    foreach ($row in $sheetXml.worksheet.sheetData.row) {
        foreach ($cell in $row.c) {
            $ref = [string] $cell.r
            if (-not $ref) {
                continue
            }

            $valueNode = $cell.SelectSingleNode('./*[local-name()="v"]')
            $formulaNode = $cell.SelectSingleNode('./*[local-name()="f"]')
            $value = if ($valueNode) { [string] $valueNode.InnerText } else { $null }
            $cellType = if ($cell.Attributes['t']) { [string] $cell.Attributes['t'].Value } else { '' }
            if ($cellType -eq 's' -and $value) {
                $sharedIndex = [int] $value
                if ($sharedIndex -ge 0 -and $sharedIndex -lt $SharedStrings.Count) {
                    $value = $SharedStrings[$sharedIndex]
                }
            }

            $cells[$ref.ToUpperInvariant()] = @{
                value   = $value
                formula = if ($formulaNode) { [string] $formulaNode.InnerText } else { '' }
            }
        }
    }

    return $cells
}

function To-Number {
    param($Value)

    if ($null -eq $Value) {
        return $null
    }

    $text = [string] $Value
    if ([string]::IsNullOrWhiteSpace($text)) {
        return $null
    }

    $text = $text.Trim().Replace(',', '.')
    $number = 0.0
    if ([double]::TryParse($text, [System.Globalization.NumberStyles]::Float, [System.Globalization.CultureInfo]::InvariantCulture, [ref] $number)) {
        return $number
    }

    return $null
}

function Extract-ConcSlope {
    param([string] $FormulaText)

    $normalized = ([string] $FormulaText).ToUpperInvariant() -replace '\s+', ''
    if ($normalized -match 'CONC=([0-9]+(?:[.,][0-9]+)?)\*ABS') {
        return To-Number $Matches[1]
    }

    return 97.087
}

function Extract-KadarConstants {
    param([string] $Formula)

    $defaults = @{
        temperatureOffset = 273.0
        pressureFactor = 760.0
        denominatorTemperature = 298.0
        massDivisor = 1000.0
    }

    $normalized = ([string] $Formula).ToUpperInvariant() -replace '\s+', ''
    if (-not $normalized) {
        return $defaults
    }

    if ($normalized -match '\([0-9]+(?:\.[0-9]+)?\+[A-Z]+\d+\)\*([0-9]+(?:\.[0-9]+)?)/\([A-Z]+\d+\*[A-Z]+\d+\*([0-9]+(?:\.[0-9]+)?)\*([0-9]+(?:\.[0-9]+)?)\*[A-Z]+\d+\)') {
        $defaults.pressureFactor = [double] $Matches[1]
        $defaults.denominatorTemperature = [double] $Matches[2]
        $defaults.massDivisor = [double] $Matches[3]
    }

    if ($normalized -match '\(([0-9]+(?:\.[0-9]+)?)\+[A-Z]+\d+\)') {
        $defaults.temperatureOffset = [double] $Matches[1]
    }

    return $defaults
}

function Get-CellValue {
    param(
        [hashtable] $Cells,
        [string] $Reference,
        [string] $Field = 'value'
    )

    $key = $Reference.ToUpperInvariant()
    if (-not $Cells.ContainsKey($key)) {
        return $null
    }

    $cell = $Cells[$key]
    if ($null -eq $cell) {
        return $null
    }

    return $cell[$Field]
}

$zip = [System.IO.Compression.ZipFile]::OpenRead($Path)
try {
    $sharedStrings = Read-SharedStrings -Zip $zip
    $worksheet = Resolve-Worksheet -Zip $zip
    $cells = Read-WorksheetCells -Zip $zip -SheetPath $worksheet.sheetPath -SharedStrings $sharedStrings

    $formulaConc = [string] (Get-CellValue -Cells $cells -Reference 'A23')
    $concSlope = Extract-ConcSlope -FormulaText $formulaConc
    $formulaKadar = [string] (Get-CellValue -Cells $cells -Reference 'I36' -Field 'formula')
    if (-not $formulaKadar) {
        $formulaKadar = [string] (Get-CellValue -Cells $cells -Reference 'I31' -Field 'formula')
    }
    $constants = Extract-KadarConstants -Formula $formulaKadar

    $result = @{
        sheetName = $worksheet.sheetName
        formulaConc = $formulaConc
        concSlope = $concSlope
        formulaKadar = $formulaKadar
        lod = @{
            kons = To-Number (Get-CellValue -Cells $cells -Reference 'C36')
            vol = To-Number (Get-CellValue -Cells $cells -Reference 'D36')
            fr = To-Number (Get-CellValue -Cells $cells -Reference 'E36')
            waktu = To-Number (Get-CellValue -Cells $cells -Reference 'F36')
            tm = To-Number (Get-CellValue -Cells $cells -Reference 'G36')
            p = To-Number (Get-CellValue -Cells $cells -Reference 'H36')
            kadar = To-Number (Get-CellValue -Cells $cells -Reference 'I36')
        }
        sample = @{
            kons = To-Number (Get-CellValue -Cells $cells -Reference 'C31')
            vol = To-Number (Get-CellValue -Cells $cells -Reference 'D31')
            fr = To-Number (Get-CellValue -Cells $cells -Reference 'E31')
            waktu = To-Number (Get-CellValue -Cells $cells -Reference 'F31')
            tm = To-Number (Get-CellValue -Cells $cells -Reference 'G31')
            p = To-Number (Get-CellValue -Cells $cells -Reference 'H31')
            kadar = To-Number (Get-CellValue -Cells $cells -Reference 'I31')
        }
        constants = $constants
    }

    $result | ConvertTo-Json -Compress -Depth 6
} finally {
    $zip.Dispose()
}

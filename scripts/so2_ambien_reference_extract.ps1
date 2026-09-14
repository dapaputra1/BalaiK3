param(
    [Parameter(Mandatory = $true)]
    [string]$Path
)

$ErrorActionPreference = 'Stop'

function Get-CellText {
    param(
        [Parameter(Mandatory = $true)] $Worksheet,
        [Parameter(Mandatory = $true)] [int]$Row,
        [Parameter(Mandatory = $true)] [int]$Column
    )

    return [string]($Worksheet.Cells.Item($Row, $Column).Text)
}

function Get-CellValue {
    param(
        [Parameter(Mandatory = $true)] $Worksheet,
        [Parameter(Mandatory = $true)] [int]$Row,
        [Parameter(Mandatory = $true)] [int]$Column
    )

    return $Worksheet.Cells.Item($Row, $Column).Value2
}

function Find-TargetWorksheet {
    param(
        [Parameter(Mandatory = $true)] $Workbook
    )

    foreach ($sheet in $Workbook.Worksheets) {
        $usedRange = $null
        try {
            $usedRange = $sheet.UsedRange
            $rowCount = [int]$usedRange.Rows.Count
            $colCount = [int]$usedRange.Columns.Count
            $flatValues = New-Object System.Collections.Generic.List[string]

            for ($row = 1; $row -le $rowCount; $row++) {
                for ($col = 1; $col -le $colCount; $col++) {
                    $text = (Get-CellText -Worksheet $sheet -Row $row -Column $col).Trim()
                    if ($text -ne '') {
                        [void]$flatValues.Add($text)
                    }
                }
            }

            $joined = ($flatValues -join ' ').ToUpperInvariant()
            if ($joined.Contains('SULFUR DIOKSIDA (SO2)') -and $joined.Contains('KADAR SO2') -and $joined.Contains('MDL')) {
                return $sheet
            }
        } finally {
            if ($usedRange) {
                [System.Runtime.Interopservices.Marshal]::ReleaseComObject($usedRange) | Out-Null
            }
        }
    }

    throw 'Worksheet acuan SO2 Ambien tidak ditemukan di workbook.'
}

function Find-CalculationHeaderRow {
    param(
        [Parameter(Mandatory = $true)] $Worksheet
    )

    $usedRange = $Worksheet.UsedRange
    try {
        $rowCount = [int]$usedRange.Rows.Count
        for ($row = 1; $row -le $rowCount; $row++) {
            $c2 = (Get-CellText -Worksheet $Worksheet -Row $row -Column 2).Trim().ToUpperInvariant()
            $c3 = (Get-CellText -Worksheet $Worksheet -Row $row -Column 3).Trim().ToUpperInvariant()
            if ($c2 -eq 'LOKASI' -and $c3.StartsWith('KONS')) {
                return $row
            }
        }
    } finally {
        [System.Runtime.Interopservices.Marshal]::ReleaseComObject($usedRange) | Out-Null
    }

    throw 'Header tabel hasil perhitungan SO2 Ambien tidak ditemukan.'
}

function Find-MdlRow {
    param(
        [Parameter(Mandatory = $true)] $Worksheet,
        [Parameter(Mandatory = $true)] [int]$StartRow
    )

    $usedRange = $Worksheet.UsedRange
    try {
        $rowCount = [int]$usedRange.Rows.Count
        for ($row = $StartRow; $row -le $rowCount; $row++) {
            $label = (Get-CellText -Worksheet $Worksheet -Row $row -Column 2).Trim().ToUpperInvariant()
            if ($label -eq 'MDL') {
                return $row
            }
        }
    } finally {
        [System.Runtime.Interopservices.Marshal]::ReleaseComObject($usedRange) | Out-Null
    }

    throw 'Baris MDL SO2 Ambien tidak ditemukan.'
}

function Find-SampleRow {
    param(
        [Parameter(Mandatory = $true)] $Worksheet,
        [Parameter(Mandatory = $true)] [int]$StartRow,
        [Parameter(Mandatory = $true)] [int]$EndRow
    )

    for ($row = $StartRow; $row -lt $EndRow; $row++) {
        $noText = (Get-CellText -Worksheet $Worksheet -Row $row -Column 1).Trim()
        $label = (Get-CellText -Worksheet $Worksheet -Row $row -Column 2).Trim().ToUpperInvariant()
        if ($label -eq '' -or $label -eq 'MDL') {
            continue
        }
        if ($noText -match '^\d+$') {
            return $row
        }
    }

    throw 'Baris sampel SO2 Ambien tidak ditemukan.'
}

function Convert-ToReferenceRow {
    param(
        [Parameter(Mandatory = $true)] $Worksheet,
        [Parameter(Mandatory = $true)] [int]$Row
    )

    return @{
        label = (Get-CellText -Worksheet $Worksheet -Row $Row -Column 2).Trim()
        kons = Get-CellValue -Worksheet $Worksheet -Row $Row -Column 3
        vol = Get-CellValue -Worksheet $Worksheet -Row $Row -Column 4
        fr = Get-CellValue -Worksheet $Worksheet -Row $Row -Column 5
        waktu = Get-CellValue -Worksheet $Worksheet -Row $Row -Column 6
        sk = Get-CellValue -Worksheet $Worksheet -Row $Row -Column 7
        pm = Get-CellValue -Worksheet $Worksheet -Row $Row -Column 8
        ppm = Get-CellValue -Worksheet $Worksheet -Row $Row -Column 9
        ugm3 = Get-CellValue -Worksheet $Worksheet -Row $Row -Column 10
    }
}

if (-not (Test-Path -LiteralPath $Path)) {
    throw "File acuan tidak ditemukan: $Path"
}

$excel = $null
$workbook = $null
$worksheet = $null

try {
    $excel = New-Object -ComObject Excel.Application
    $excel.Visible = $false
    $excel.DisplayAlerts = $false
    $workbook = $excel.Workbooks.Open($Path)
    $worksheet = Find-TargetWorksheet -Workbook $workbook

    $headerRow = Find-CalculationHeaderRow -Worksheet $worksheet
    $mdlRow = Find-MdlRow -Worksheet $worksheet -StartRow ($headerRow + 1)
    $sampleRow = Find-SampleRow -Worksheet $worksheet -StartRow ($headerRow + 1) -EndRow $mdlRow

    $mdl = Convert-ToReferenceRow -Worksheet $worksheet -Row $mdlRow
    $sample = Convert-ToReferenceRow -Worksheet $worksheet -Row $sampleRow

    $factorPpm = $null
    if ($mdl.kons -and $mdl.vol -and $mdl.fr -and $mdl.waktu -and $mdl.sk -and $mdl.pm -and $mdl.ppm) {
        $numerator = [double]$mdl.ppm * [double]$mdl.fr * [double]$mdl.waktu * 298 * [double]$mdl.pm
        $denominator = [double]$mdl.kons * (([double]$mdl.vol) / 10) * (273 + [double]$mdl.sk) * 760
        if ($denominator -ne 0) {
            $factorPpm = $numerator / $denominator
        }
    }

    $factorUgm3 = $null
    if ($mdl.ppm -and [double]$mdl.ppm -ne 0 -and $mdl.ugm3) {
        $factorUgm3 = ([double]$mdl.ugm3) / ([double]$mdl.ppm)
    }

    $result = @{
        workbookPath = $Path
        sheetName = $worksheet.Name
        mdl = $mdl
        sample = $sample
        formulaPpm = [string]$worksheet.Cells.Item($mdlRow, 9).Formula
        formulaUgm3 = [string]$worksheet.Cells.Item($mdlRow, 10).Formula
        factorPpm = $factorPpm
        factorUgm3 = $factorUgm3
    }

    $result | ConvertTo-Json -Depth 6 -Compress
} finally {
    if ($worksheet) {
        [System.Runtime.Interopservices.Marshal]::ReleaseComObject($worksheet) | Out-Null
    }
    if ($workbook) {
        $workbook.Close($false)
        [System.Runtime.Interopservices.Marshal]::ReleaseComObject($workbook) | Out-Null
    }
    if ($excel) {
        $excel.Quit()
        [System.Runtime.Interopservices.Marshal]::ReleaseComObject($excel) | Out-Null
    }
    [gc]::Collect()
    [gc]::WaitForPendingFinalizers()
}

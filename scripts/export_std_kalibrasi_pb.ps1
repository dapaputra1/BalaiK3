param(
    [Parameter(Mandatory = $true)]
    [string]$TemplatePath,

    [Parameter(Mandatory = $true)]
    [string]$OutputPath,

    [Parameter(Mandatory = $true)]
    [string]$PayloadPath,

    [Parameter(Mandatory = $false)]
    [string]$LogoPath = ''
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function To-NullableDouble {
    param([object]$Value)

    if ($null -eq $Value) {
        return $null
    }

    $text = [string]$Value
    if ([string]::IsNullOrWhiteSpace($text)) {
        return $null
    }

    $normalized = $text.Trim().Replace(' ', '')
    $hasComma = $normalized.Contains(',')
    $hasDot = $normalized.Contains('.')

    if ($hasComma -and $hasDot) {
        if ($normalized.LastIndexOf(',') -gt $normalized.LastIndexOf('.')) {
            $normalized = $normalized.Replace('.', '').Replace(',', '.')
        } else {
            $normalized = $normalized.Replace(',', '')
        }
    } elseif ($hasComma) {
        $normalized = $normalized.Replace(',', '.')
    }

    $number = 0.0
    if ([double]::TryParse($normalized, [System.Globalization.NumberStyles]::Any, [System.Globalization.CultureInfo]::InvariantCulture, [ref]$number)) {
        return $number
    }

    return $null
}

function To-ExcelTimeSerial {
    param([object]$Value)

    $text = [string]$Value
    if ($null -eq $Value) {
        $text = ''
    }
    $text = $text.Trim()
    if ($text -eq '') {
        return $null
    }

    $match = [regex]::Match($text, '^(?<h>\d{1,2}):(?<m>\d{2})(:(?<s>\d{2}))?$')
    if ($match.Success) {
        $hours = [int]$match.Groups['h'].Value
        $minutes = [int]$match.Groups['m'].Value
        $seconds = if ($match.Groups['s'].Success) { [int]$match.Groups['s'].Value } else { 0 }
        return (($hours * 3600) + ($minutes * 60) + $seconds) / 86400
    }

    return To-NullableDouble $Value
}

function Get-StringOrDefault {
    param(
        [object]$Value,
        [string]$Default = ''
    )

    if ($null -eq $Value) {
        return $Default
    }

    $text = [string]$Value
    if ([string]::IsNullOrWhiteSpace($text)) {
        return $Default
    }

    return $text
}

function Set-CellString {
    param(
        $Worksheet,
        [string]$Address,
        [string]$Value
    )

    $Worksheet.Range($Address).Value2 = $Value
}

function Set-CellNumber {
    param(
        $Worksheet,
        [string]$Address,
        [object]$Value
    )

    if ($null -eq $Value -or [string]::IsNullOrWhiteSpace([string]$Value)) {
        $Worksheet.Range($Address).ClearContents() | Out-Null
        return
    }

    $Worksheet.Range($Address).Value2 = [double]$Value
}

$payload = Get-Content -LiteralPath $PayloadPath -Raw | ConvertFrom-Json

$excel = $null
$workbook = $null
$worksheet = $null
$chartObject = $null
$logoPicture = $null

function Replace-HeaderLogo {
    param(
        $Worksheet,
        [string]$ImagePath
    )

    if ([string]::IsNullOrWhiteSpace($ImagePath) -or -not (Test-Path -LiteralPath $ImagePath)) {
        return $null
    }

    $picture = $null
    try {
        $pictures = $Worksheet.Pictures()
        if ($null -ne $pictures -and $pictures.Count -ge 1) {
            $picture = $pictures.Item(1)
        }
    } catch {
        $picture = $null
    }

    if ($null -eq $picture) {
        return $null
    }

    $left = $picture.Left
    $top = $picture.Top
    $width = $picture.Width
    $height = $picture.Height
    $picture.Delete()
    [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($picture)

    return $Worksheet.Shapes.AddPicture($ImagePath, $false, $true, $left, $top, $width, $height)
}

try {
    $excel = New-Object -ComObject Excel.Application
    $excel.Visible = $false
    $excel.DisplayAlerts = $false
    $excel.AskToUpdateLinks = $false
    $excel.ScreenUpdating = $false
    $excel.EnableEvents = $false

    $workbook = $excel.Workbooks.Open($TemplatePath, 0, $false)
    $worksheet = $workbook.Worksheets.Item(1)
    $logoPicture = Replace-HeaderLogo -Worksheet $worksheet -ImagePath $LogoPath

    Set-CellString $worksheet 'D7' (': ' + (Get-StringOrDefault $payload.parameter_name 'Timbal (Pb)'))
    Set-CellString $worksheet 'D8' (': ' + (Get-StringOrDefault $payload.sample_type 'LK'))
    Set-CellString $worksheet 'D9' ': Baik'
    Set-CellString $worksheet 'D10' (': ' + (Get-StringOrDefault $payload.receipt_date ''))
    Set-CellString $worksheet 'D11' (': ' + (Get-StringOrDefault $payload.analysis_date ''))
    Set-CellString $worksheet 'D12' (': ' + (Get-StringOrDefault $payload.analis_name '-'))

    $rows = @($payload.rows)
    for ($i = 0; $i -lt 6; $i++) {
        $rowNumber = 17 + $i
        $row = if ($i -lt $rows.Count) { $rows[$i] } else { $null }

        $sampleNo = if ($null -ne $row -and -not [string]::IsNullOrWhiteSpace([string]$row.no_sampel)) { [string]$row.no_sampel } else { [string]($i + 1) }
        Set-CellString $worksheet ("B{0}" -f $rowNumber) $sampleNo
        Set-CellNumber $worksheet ("C{0}" -f $rowNumber) (To-NullableDouble ($row.volume))
        Set-CellNumber $worksheet ("D{0}" -f $rowNumber) (To-ExcelTimeSerial ($row.waktu_baca))
        Set-CellNumber $worksheet ("E{0}" -f $rowNumber) (To-NullableDouble ($row.hasil_baca))
        Set-CellNumber $worksheet ("F{0}" -f $rowNumber) (To-NullableDouble ($row.kandungan))

        $ket = Get-StringOrDefault $row.keterangan ''
        if ([string]::IsNullOrWhiteSpace($ket)) {
            $worksheet.Range("G$rowNumber").ClearContents() | Out-Null
        } else {
            Set-CellString $worksheet ("G{0}" -f $rowNumber) $ket
        }
    }

    Set-CellNumber $worksheet 'G27' (To-NullableDouble ($payload.curve_y))
    Set-CellNumber $worksheet 'G28' (To-NullableDouble ($payload.curve_x))

    $worksheet.Range('G27').NumberFormat = '0.0000'
    $worksheet.Range('G28').NumberFormat = '0.000'
    $worksheet.Range('D17:D22').NumberFormat = 'hh:mm'

    try {
        $chartObject = $worksheet.ChartObjects().Item(1)
        if ($null -ne $chartObject) {
            $chartObject.Chart.HasTitle = $true
            $chartObject.Chart.ChartTitle.Text = 'kurva kalibrasi ' + (Get-StringOrDefault $payload.curve_label 'Pb')
        }
    } catch {
    }

    if (Test-Path -LiteralPath $OutputPath) {
        Remove-Item -LiteralPath $OutputPath -Force
    }

    $workbook.SaveAs($OutputPath, 51)
    Write-Output $OutputPath
}
finally {
    if ($null -ne $logoPicture) {
        [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($logoPicture)
    }
    if ($null -ne $chartObject) {
        [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($chartObject)
    }
    if ($null -ne $workbook) {
        $workbook.Close($false)
        [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($workbook)
    }
    if ($null -ne $worksheet) {
        [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($worksheet)
    }
    if ($null -ne $excel) {
        $excel.Quit()
        [void][System.Runtime.InteropServices.Marshal]::ReleaseComObject($excel)
    }
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}

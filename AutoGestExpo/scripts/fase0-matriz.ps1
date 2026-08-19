# Fase 0 — Matriz de aceptacion API movil (AutoGest 1.3.0)
# Uso: .\scripts\fase0-matriz.ps1
#      .\scripts\fase0-matriz.ps1 -BaseUrl "https://autogest-jlm7.onrender.com/api"

param(
    [string]$BaseUrl = "http://192.168.1.9/AutoGest/public/api"
)

$roles = @(
    @{
        email = "cliente1@autogest.test"
        role  = "cliente"
        tab   = "GET /vehicles"
        path  = "/vehicles"
    },
    @{
        email = "asesor1@autogest.test"
        role  = "asesor"
        tab   = "GET /appointments?status=pendiente"
        path  = "/appointments?status=pendiente"
    },
    @{
        email = "mecanico1@autogest.test"
        role  = "mecanico"
        tab   = "GET /orders"
        path  = "/orders"
    },
    @{
        email = "admin@autogest.test"
        role  = "admin"
        tab   = "GET /users"
        path  = "/users"
    }
)

function Invoke-ApiStep {
    param(
        [string]$Method,
        [string]$Path,
        [string]$Token = $null,
        [object]$Body = $null
    )
    $headers = @{ Accept = "application/json" }
    if ($Token) { $headers.Authorization = "Bearer $Token" }
    $uri = "$BaseUrl$Path"
    $sw = [Diagnostics.Stopwatch]::StartNew()
    try {
        $params = @{
            Uri         = $uri
            Method      = $Method
            Headers     = $headers
            TimeoutSec  = 45
            UseBasicParsing = $true
        }
        if ($Body) {
            $params.Body = ($Body | ConvertTo-Json)
            $params.ContentType = "application/json"
        }
        $r = Invoke-WebRequest @params
        $sw.Stop()
        $json = $null
        try { $json = $r.Content | ConvertFrom-Json } catch { }
        return @{
            ok     = $true
            status = [int]$r.StatusCode
            ms     = $sw.ElapsedMilliseconds
            json   = $json
        }
    }
    catch {
        $sw.Stop()
        $status = $null
        if ($_.Exception.Response) { $status = [int]$_.Exception.Response.StatusCode }
        return @{
            ok     = $false
            status = $status
            ms     = $sw.ElapsedMilliseconds
            error  = $_.Exception.Message
        }
    }
}

Write-Host "`n=== Fase 0 matriz ===" -ForegroundColor Cyan
Write-Host "API: $BaseUrl`n"

$matrix = @()
foreach ($account in $roles) {
    $row = [ordered]@{
        email    = $account.email
        role     = $account.role
        login    = $null
        dashboard = $null
        tab      = $null
        logout   = $null
    }

    $login = Invoke-ApiStep -Method POST -Path "/login" -Body @{ email = $account.email; password = "password" }
    $row.login = if ($login.ok) { "OK $($login.ms)ms role=$($login.json.user.role)" } else { "FAIL $($login.status) $($login.error)" }

    if (-not $login.ok) {
        $matrix += [pscustomobject]$row
        continue
    }

    $token = $login.json.token
    $dash = Invoke-ApiStep -Method GET -Path "/dashboard" -Token $token
    if ($dash.ok) {
        $stats = ($dash.json.stats | ConvertTo-Json -Compress)
        $row.dashboard = "OK $($dash.ms)ms role=$($dash.json.role) stats=$stats"
    }
    else {
        $row.dashboard = "FAIL $($dash.status) $($dash.error)"
    }

    $tab = Invoke-ApiStep -Method GET -Path $account.path -Token $token
    if ($tab.ok) {
        $count = $null
        if ($account.path -match "vehicles") { $count = @($tab.json.vehicles).Count }
        elseif ($account.path -match "appointments") { $count = @($tab.json.appointments).Count }
        elseif ($account.path -match "orders") { $count = @($tab.json.orders).Count }
        elseif ($account.path -match "users") { $count = @($tab.json.users).Count }
        $row.tab = "OK $($tab.ms)ms $($account.tab) count=$count"
    }
    else {
        $row.tab = "FAIL $($tab.status) $($account.tab)"
    }

    $out = Invoke-ApiStep -Method POST -Path "/logout" -Token $token
    $row.logout = if ($out.ok) { "OK $($out.ms)ms" } else { "FAIL $($out.status)" }

    $matrix += [pscustomobject]$row
}

$matrix | Format-Table -AutoSize
$pass = ($matrix | Where-Object { $_.login -like "OK*" -and $_.dashboard -like "OK*" -and $_.tab -like "OK*" -and $_.logout -like "OK*" }).Count
Write-Host "`nResultado: $pass/4 roles PASS en $BaseUrl" -ForegroundColor $(if ($pass -eq 4) { "Green" } else { "Yellow" })

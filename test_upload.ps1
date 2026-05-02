# PowerShell script to test file upload
$token = "3|nrQMI9sklakJOAArN8oJ0PjMJRMKVxkhUDTB9N4n61447030"
$apiUrl = "http://localhost:8000/api/v1/upload/logo"
$filePath = "temp_logo.png"

# Read file content
$fileContent = [System.IO.File]::ReadAllBytes($filePath)

# Create multipart form data
$boundary = [System.Guid]::NewGuid().ToString()
$LF = "`r`n"
$bodyLines = @()

# Add file part
$bodyLines += "--$boundary"
$bodyLines += "Content-Disposition: form-data; name=`"file`"; filename=`"$(Split-Path $filePath -Leaf)`""
$bodyLines += "Content-Type: image/png"
$bodyLines += ""
$bodyLines += ""
$bodyBytes = [System.Text.Encoding]::UTF8.GetBytes([string]::Join($LF, $bodyLines))
$fileBytes = $fileContent
$boundaryBytes = [System.Text.Encoding]::UTF8.GetBytes("$LF--$boundary--$LF")

# Combine all parts
$body = $bodyBytes + $fileBytes + $boundaryBytes

# Create request
$webRequest = [System.Net.HttpWebRequest]::Create($apiUrl)
$webRequest.Method = "POST"
$webRequest.ContentType = "multipart/form-data; boundary=$boundary"
$webRequest.Headers.Add("Authorization", "Bearer $token")
$webRequest.ContentLength = $body.Length

# Write request body
$requestStream = $webRequest.GetRequestStream()
$requestStream.Write($body, 0, $body.Length)
$requestStream.Close()

# Get response
try {
    $response = $webRequest.GetResponse()
    $responseStream = $response.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($responseStream)
    $result = $reader.ReadToEnd()
    $reader.Close()
    $responseStream.Close()
    $response.Close()
    
    Write-Host "Response: $result"
} catch {
    if ($_.Exception.Response) {
        $errorResponse = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($errorResponse)
        $errorText = $reader.ReadToEnd()
        $reader.Close()
        
        Write-Host "Error Response: $errorText"
    } else {
        Write-Host "Error: $($_.Exception.Message)"
    }
}

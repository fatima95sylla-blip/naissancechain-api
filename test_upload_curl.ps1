# Test upload using curl
$token = "3|nrQMI9sklakJOAArN8oJ0PjMJRMKVxkhUDTB9N4n61447030"
$apiUrl = "http://localhost:8000/api/v1/upload/logo"
$filePath = "temp_logo.png"

# Use curl for upload
curl.exe -X POST $apiUrl `
  -H "Authorization: Bearer $token" `
  -F "file=@$filePath" `
  -F "type=logo"

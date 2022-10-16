Request Dump:
Direction:
{!! "\t" !!}{{$direction}}
Type:
{!! "\t" !!}{{$type}}
Url:
{!! "\t" !!}{{$url}}
Execution Time:
{!! "\t" !!}{{$execution_time}}
HTTP Code:
{!! "\t" !!}{{$http_code}}
HTTP Method:
{!! "\t" !!}{{$http_method}}
HTTP Protocol Version:
{!! "\t" !!}{{$http_protocol_version}}
Request Headers:
{!! $formatted_request_headers !!}
Request:
{!! "\t" !!}{!! $request !!}
Response Headers:
{!! $formatted_response_headers !!}
Response:
{!! "\t" !!}{!! $response !!}

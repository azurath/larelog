Request Dump:
Direction:
    {{$direction}}
Type:
    {{$type}}
Url:
    {{$url}}
Execution Time:
    {{$executionTime}}
HTTP Code:
    {{$httpCode}}
HTTP Method:
    {{$httpMethod}}
HTTP Protocol Version:
    {{$httpProtocolVersion}}
Request Headers:
    {!! $formattedRequestHeaders !!}
Request:
    {!! $request !!}
Response Headers:
    {!! $formattedResponseHeaders !!}
Response:
    {!! $response !!}

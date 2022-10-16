Request Dump:
Started At: {{$logItem->started_at}}
Execution Time: {{$logItem->formatted_execution_time}} seconds.
Direction: {{$logItem->direction}}
Type: {{$logItem->type}}
Url: {{$logItem->url}}
HTTP Code: {{$logItem->http_code}}
HTTP Method: {{$logItem->http_method}}
HTTP Protocol Version: {{$logItem->http_protocol_version}}
@if($logItem->user_model)
User Model: {{$logItem->user_model}}
@endif
@if($logItem->user_id)
User Id: {{$logItem->user_id}}
@endif
Request Headers:
@if($logItem->request_headers)
{!! $logItem->formatted_request_headers !!}
@endif
Request:
@if($logItem->request)
{!! "\t" !!}{!! $logItem->request !!}
@endif
Response Headers:
@if($logItem->response_headers)
{!! $logItem->formatted_response_headers !!}
@endif
Response:
@if($logItem->response)
{!! "\t" !!}{!! $logItem->response !!}
@endif

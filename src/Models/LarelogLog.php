<?php

namespace Azurath\Larelog\Models;

use Azurath\Larelog\Larelog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;

/**
 * App\Order
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property string $direction
 * @property string $type
 * @property string $http_method
 * @property string $http_protocol_version
 * @property string $url
 * @property string $http_code
 * @property string $request_headers
 * @property string $request
 * @property string $response_headers
 * @property string $response
 * @property string $execution_time
 */
class LarelogLog extends Model
{
    const UPDATED_AT = null;

    public $timestamps = [
        'created_at',
    ];

    protected $fillable = [
        'direction',
        'type',
        'url',
        'http_code',
        'http_method',
        'http_protocol_version',
        'request_headers',
        'request',
        'response_headers',
        'response',
        'execution_time',
    ];

    public function toString(): ?string
    {
        return $this->formatAsText();
    }

    public function formatAsText(): ?string
    {
        $formattedRequestHeaders = Larelog::formatLogHeaders($this->request_headers);
        $formattedResponseHeaders = Larelog::formatLogHeaders($this->response_headers);
        $data = array_merge($this->toArray(),
            [
                'formatted_request_headers' => $formattedRequestHeaders,
                'formatted_response_headers' => $formattedResponseHeaders,
            ]
        );

        return View::make('larelog::log.log', $data)->render();
    }
}

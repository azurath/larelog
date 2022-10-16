<?php

namespace Azurath\Larelog\Models;

use Azurath\Larelog\Larelog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
 * @property int $user_id
 * @property string $user_model
 * @property-read bool $has_user
 * @property-read ?string $formatted_request_headers
 * @property-read ?string $formatted_response_headers
 * @property-read string $formatted_execution_time
 */
class LarelogItem extends Model
{
    const UPDATED_AT = null;

    public $timestamps = [
        'created_at',
    ];

    protected $fillable = [
        'started_at',
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
        'user_id',
        'user_model',
    ];

    protected $appends = [
        'formatted_request_headers',
        'formatted_response_headers',
        'formatted_execution_time',
    ];

    protected $casts = [
        'started_at' => 'datetime',
    ];

    public function user(): ?BelongsTo
    {
        return $this->has_user
            ? $this->belongsTo($this->user_model, 'user_id', 'id')
            : new BelongsTo($this->newQuery(), $this, '', '', '');
    }

    public function getHasUserAttribute(): bool
    {
        return $this->user_id && $this->user_model;
    }

    public function toString(): ?string
    {
        return $this->formatAsText();
    }

    public function getFormattedRequestHeadersAttribute(): ?string
    {
        return Larelog::formatLogHeaders($this->request_headers);
    }

    public function getFormattedResponseHeadersAttribute(): ?string
    {
        return Larelog::formatLogHeaders($this->response_headers);
    }

    public function getFormattedExecutionTimeAttribute(): ?string
    {
        return number_format($this->execution_time, 4);
    }

    public function formatAsText(): ?string
    {
        return View::make('larelog::log.log', ['logItem' => $this])->render();
    }
}

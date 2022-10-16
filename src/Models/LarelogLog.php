<?php

namespace Azurath\Larelog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
}

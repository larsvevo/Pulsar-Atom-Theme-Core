<?php

namespace Atom\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class WebsiteHomeItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'website_home_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'image_url',
        'website_home_category_id',
        'maximum_purchases',
        'type',
        'count',
        'price',
        'currency_type',
        'currency_price',
        'data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'object',
    ];

    /**
     * The attributes that should be appended.
     *
     * @var array
     */
    protected $appends = ['image'];

    /**
     * Get the website home category that owns the item.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(WebsiteHomeCategory::class, 'website_home_category_id');
    }

    /**
     * Get the permission for the item.
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Get all of the users that are assigned this item.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('id', 'left', 'top', 'z', 'data')
            ->withTimestamps();
    }

    /**
     * Get the image attribute.
     */
    public function getImageAttribute(): string
    {
        return Storage::url($this->image_url);
    }

    /**
     * Set the data attribute.
     */
    public function setDataAttibute($value): void
    {
        $this->attributes['data'] = ! is_null($value) ? (object) $value : (object) [];
    }

    /**
     * Get the pivot data attribute.
     */
    public function getPivotDataAttribute(): object
    {
        return json_decode($this->pivot->data);
    }

    /**
     * Get the avatar attribute.
     */
    public function getAvatarAttribute(): string
    {
        $action = array_filter([
            $this->pivotData->action ?? 'std',
            optional($this->pivotData)->waving ? 'wav' : null,
            optional($this->pivotData)->item ? "crr={$this->pivotData->item}" : null,
        ]);

        return http_build_query([
            'img_format' => 'gif',
            'size' => $this->pivotData->size ?? 'm',
            'head_direction' => $this->pivotData->head_direction ?? 3,
            'direction' => $this->pivotData->direction ?? 3,
            'gesture' => $this->pivotData->gesture ?? 'std',
            'action' => implode(',', $action),
            'headonly' => $this->pivotData->headonly ?? 0,
        ]);
    }
}

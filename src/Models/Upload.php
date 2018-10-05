<?php

namespace Oxygencms\Uploads\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Upload extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'filename',
        'path',
        'size',
        'intent',
    ];

    protected $appends = [
        'public_path',
        'update_url'
    ];

    /**
     * By default sort the uploads by order_id
     * then by created_at (default).
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('order_id', function(Builder $builder) {
            $builder->orderBy('order_id');
        });
    }

    /**
     * Get all of the owning uploadable models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function uploadable()
    {
        return $this->morphTo();
    }

    /**
     * @return string
     */
    public function getPublicPathAttribute()
    {
        return "/$this->path/$this->filename";
    }

    /**
     * @return string
     */
    protected function getUpdateUrlAttribute()
    {
        return route('upload.update', $this->id);
    }
}

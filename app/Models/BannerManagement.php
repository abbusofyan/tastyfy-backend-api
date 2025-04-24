<?php

namespace App\Models;

use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BannerManagement extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'url',
        'media_id',
        'group',
        'order'
    ];

    protected $table = 'banner_management';

    public function fullmedia()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}

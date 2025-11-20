# Cloudinary Manager for Laravel

A professional Laravel package for managing media uploads with Cloudinary.

## Installation
```bash
composer require youssefreda4/cloudinary-manager
```

## Configuration

Publish the configuration file:
```bash
php artisan vendor:publish --tag=cloudinary-config
```

Add to your `.env`:
```env
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

## Usage

### Using Facade
```php
use CloudinaryManager\Facades\Cloudinary;

// Upload image
$result = Cloudinary::uploadImage($request->file('image'), 'products');

// Delete image
Cloudinary::deleteImage('products/image_id');

// Generate URL with transformations
$url = Cloudinary::generateUrl('products/image_id', [
    'width' => 300,
    'height' => 300,
    'crop' => 'fill'
]);
```

### Using Trait in Models
```php
use CloudinaryManager\Traits\HasCloudinaryMedia;

class Product extends Model
{
    use HasCloudinaryMedia;

    public function uploadProductImage($file)
    {
        $result = $this->uploadToCloudinary($file, 'image', 'products');
        $this->image_public_id = $result->publicId;
        $this->image_url = $result->secureUrl;
        $this->save();
    }
}
```

### API Endpoints

The package automatically registers these routes:

- `POST /api/cloudinary/upload/image`
- `POST /api/cloudinary/upload/video`
- `DELETE /api/cloudinary/delete/image`
- `DELETE /api/cloudinary/delete/video`
- `GET /api/cloudinary/resource/info`

## License

MIT

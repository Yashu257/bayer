<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use Core\Http\Request;
use Core\Http\Response;
use App\Models\Selfie;

class SelfieController
{
    public function upload(Request $request): Response
    {
        try {
            $body = $request->isJson() ? $request->json() : $request->all();

            // Validate required fields
            if (!isset($body['image']) || empty($body['image'])) {
                return Response::error('Image is required.', 422);
            }

            // Decode base64 image
            $imageData = $body['image'];
            if (preg_match('/^data:image\/(png|jpeg|jpg);base64,/', $imageData, $type)) {
                $imageData = preg_replace('/^data:image\/(png|jpeg|jpg);base64,/', '', $imageData);
                $imageData = base64_decode($imageData);
                $extension = $type[1] === 'jpeg' ? 'jpg' : $type[1];
            } else {
                return Response::error('Invalid image format.', 422);
            }

            // Generate a unique filename
            $fileName = 'selfie_' . uniqid() . '.' . $extension;
            $uploadDir = __DIR__ . '/../../../public/uploads/selfies/';
            $filePath = $uploadDir . $fileName;
            $publicPath = '/uploads/selfies/' . $fileName;

            // Ensure directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Save file
            if (file_put_contents($filePath, $imageData) === false) {
                return Response::error('Failed to save image.', 500);
            }

            // Save to database
            // For preview, we'll set event_id to 1 (default)
            $selfie = new Selfie([
                'event_id' => 1,
                'image_path' => $publicPath,
                'image_name' => $fileName,
            ]);
            $selfie->save();

            return Response::success([
                'image_path' => $publicPath,
                'image_name' => $fileName,
            ], 'Selfie saved successfully!');

        } catch (\Exception $e) {
            return Response::error($e->getMessage(), 500);
        }
    }
}

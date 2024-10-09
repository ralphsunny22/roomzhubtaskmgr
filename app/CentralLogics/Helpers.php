<?php

namespace App\CentralLogics;
use Illuminate\Support\Facades\Storage;

class Helpers
{
    public static function error_processor($validator)
    {
        $err_keeper = [];
        foreach ($validator->errors()->getMessages() as $index => $error) {
            array_push($err_keeper, ['code' => $index, 'message' => $error[0]]);
        }
        return $err_keeper;
    }

    public static function upload(string $dir, string $format, $image, $default_image)
    {
        if ($image != null) {
            $imageName = \Carbon\Carbon::now()->toDateString() . "-" . uniqid() . "." . $format;
            if (!Storage::disk('public')->exists($dir)) {
                Storage::disk('public')->makeDirectory($dir);
            }
            Storage::disk('public')->putFileAs($dir, $image, $imageName);
        } else {
            $imageName = $default_image;
        }

        return $imageName;
    }

    public static function update(string $dir, $old_image, string $format, $image)
    {
        if ($image == null) {
            return $old_image;
        }
        if (Storage::disk('public')->exists($dir . $old_image)) {
            Storage::disk('public')->delete($dir . $old_image);
        }
        $imageName = Helpers::upload($dir, $format, $image, 'noimage.png');
        return $imageName;
    }

    public static function removeFile(string $dir, $old_image, $default_image)
    {
        if ($old_image !== $default_image) {
            if (Storage::disk('public')->exists($dir . $old_image)) {
                Storage::disk('public')->delete($dir . $old_image);
            }
        }

        //$imageName = Helpers::upload($dir, $format, $image, 'noimage.png');
        //return $imageName;
    }

}

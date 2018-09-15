<?php
/**
 * Created by PhpStorm.
 * User: Герман
 * Date: 12.08.2018
 * Time: 9:17
 */

class ImageLoader
{
    public const RESULT_ERROR_FORMAT_NOT_SUPPORTED = 1;
    public const RESULT_ERROR_NOT_SAVED = 2;
    public const RESULT_ALL_OK = 0;

    public static function load($subpath, $tempname, $imagename,$subdir, $width)
    {
        $imageFormat = pathinfo($imagename, PATHINFO_EXTENSION);

        $format = $imageFormat;
        if ($imageFormat == 'jpeg' || 'jpg')
            $imageFormat = IMAGETYPE_JPEG;
        elseif ($imageFormat == 'png')
            $imageFormat = IMAGETYPE_PNG;
        elseif ($imageFormat == 'gif')
            $imageFormat = IMAGETYPE_GIF;
        else {
            return ImageLoader::RESULT_ERROR_FORMAT_NOT_SUPPORTED;
        }

        $image = new SimpleImage();
        $image->load($tempname);
        $image->resizeToWidth($width);

        if(!is_dir(IMAGE_PATH . $subpath . '/'. $subdir)) {
            $result = mkdir(IMAGE_PATH . $subpath . '\\' . $subdir);
            $r = is_dir(IMAGE_PATH . $subpath . '/' . $subdir);
        }

        $result = $image->save(IMAGE_PATH . $subpath . '/'. $subdir .'/' . $imagename, $format);

        if($result)
            return ImageLoader::RESULT_ALL_OK;
        else{
            return ImageLoader::RESULT_ERROR_NOT_SAVED;
        }
    }

    public static function loadService($tempname, $name, $serviceId, $imageId)
    {
        $imageFormat = pathinfo($name, PATHINFO_EXTENSION);
        $filename =  ImageLoader::formImageName($imageFormat,$imageId);
        return ImageLoader::load('services',$tempname,
            $filename,$serviceId,500);
    }

    public static function loadCompanyLogotype($tempname,$name,$companyId,$imageId){
        $imageFormat = pathinfo($name, PATHINFO_EXTENSION);
        $filename =  ImageLoader::formImageName($imageFormat,$imageId);
        return ImageLoader::load('companies',$tempname,
            $filename,$companyId,200);
    }

    public static function loadUserPhoto($tempname, $name,$userId, $imageId)
    {
        $imageFormat = pathinfo($name, PATHINFO_EXTENSION);
        $filename =  ImageLoader::formImageName($imageFormat,$imageId);
        return ImageLoader::load('users',$tempname,
            $filename,$userId,750);
    }

    public static function loadEventImage($tempname, $name, $eventId,$imageId)
    {
        $imageFormat = pathinfo($name, PATHINFO_EXTENSION);
        $filename =  ImageLoader::formImageName($imageFormat,$imageId);
        return ImageLoader::load('events',$tempname,
            $filename,$eventId,750);
    }

    public static function loadReviewImage($tempname, $name,$reviewId,$imageId)
    {
        $imageFormat = pathinfo($name, PATHINFO_EXTENSION);
        $filename =  ImageLoader::formImageName($imageFormat,$imageId);
        return ImageLoader::load('reviews',$tempname,
            $filename,$reviewId,750);
    }

    public static function loadNewImage($tempname, $name,$newId,$imageId)
    {
        $imageFormat = pathinfo($name, PATHINFO_EXTENSION);
        $filename =  ImageLoader::formImageName($imageFormat,$imageId);
        return ImageLoader::load('news',$tempname,
            $filename,$newId,750);
    }

    public static function formImageName($format, $imageId)
    {
        return $imageId . '.' . $format;
    }

    public static function formFullImageName($subpath, $format, $id, $imageId)
    {
        return IMAGE_PATH_TRUNCATED. $subpath . '/'.$id.'/' .ImageLoader::formImageName($format,$imageId);
    }

    public static function delete($imageName){
        $result = unlink(BASE_PATH.$imageName);
        return $result;
    }
}
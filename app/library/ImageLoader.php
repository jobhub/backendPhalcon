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

    public static function load($subpath, $tempname, $imagename, $width)
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
        $result = $image->save(IMAGE_PATH . $subpath . '/' . $imagename, $format);

        if($result)
            return ImageLoader::RESULT_ALL_OK;
        else{
            return ImageLoader::RESULT_ERROR_NOT_SAVED;
        }
    }

    public static function loadService($tempname, $name, $countImages, $serviceId)
    {
        $imageFormat = pathinfo($name, PATHINFO_EXTENSION);
        $filename =  ImageLoader::formImageName($imageFormat,$serviceId,$countImages);
        return ImageLoader::load('services',$tempname,
            $filename,500);
    }

    public static function loadCompanyLogotype($tempname,$name,$companyId){
        $imageFormat = pathinfo($name, PATHINFO_EXTENSION);
        $filename =  ImageLoader::formImageName($imageFormat,$companyId,0);
        return ImageLoader::load('companies',$tempname,
            $filename,200);
    }

    public static function loadUserPhoto($tempname, $name, $countImages, $userId)
    {
        $imageFormat = pathinfo($name, PATHINFO_EXTENSION);
        $filename =  ImageLoader::formImageName($imageFormat,$userId,$countImages);
        return ImageLoader::load('users',$tempname,
            $filename,750);
    }

    public static function loadEventImage($tempname, $name, $eventId)
    {
        $imageFormat = pathinfo($name, PATHINFO_EXTENSION);
        $filename =  ImageLoader::formImageName($imageFormat,$eventId,0);
        return ImageLoader::load('events',$tempname,
            $filename,750);
    }

    public static function loadReviewImage($tempname, $name, $countImages, $reviewId)
    {
        $imageFormat = pathinfo($name, PATHINFO_EXTENSION);
        $filename =  ImageLoader::formImageName($imageFormat,$reviewId,$countImages);
        return ImageLoader::load('reviews',$tempname,
            $filename,750);
    }

    public static function formImageName($format, $id, $countImages)
    {
        return $id . '_' . ($countImages + 1) . '.' . $format;
    }

    public static function formFullImageName($subpath, $format, $id, $countImages)
    {
        return IMAGE_PATH_TRUNCATED. $subpath . '/' .ImageLoader::formImageName($format,$id,$countImages);
    }

    public static function delete($imageName){
        $result = unlink(BASE_PATH.$imageName);
        return $result;
    }
}
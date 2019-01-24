<?php

namespace App\Models;

use App\Libs\SupportClass;

class LikeDislikeModel extends NotDeletedModel
{
    /**
     *
     * @var string
     */
    protected $dislike_users;

    /**
     *
     * @var string
     */
    protected $like_users;

    /**
     * @return string
     */
    public function getDislikeUsers(): string
    {
        return $this->dislike_users;
    }

    /**
     * @param string $dislike_users
     */
    public function setDislikeUsers(string $dislike_users)
    {
        $this->dislike_users = $dislike_users;
    }

    /**
     * @return string
     */
    public function getLikeUsers(): string
    {
        return $this->like_users;
    }

    /**
     * @param string $like_users
     */
    public function setLikeUsers(string $like_users)
    {
        $this->like_users = $like_users;
    }

    public function like($user_id){
        $dislikes = SupportClass::to_php_array($this->dislike_users);
        $likes = SupportClass::to_php_array($this->like_users);
        if (in_array($user_id, $likes)) {
            SupportClass::deleteElement($user_id, $likes);
        }else {
            if (in_array($user_id, $dislikes)) { 
                SupportClass::deleteElement($user_id, $dislikes);
                $this->setDislikeUsers(SupportClass::to_pg_array($dislikes));
            }
            array_push($likes, $user_id);
        }
        $this->setLikeUsers(SupportClass::to_pg_array($likes));
        $this->update();
    }
    
    public function dislike($user_id){
        $dislikes = SupportClass::to_php_array($this->dislike_users);
        $likes = SupportClass::to_php_array($this->like_users);

        if (in_array($user_id, $dislikes)) {
            // If user like the rast deleted like
            SupportClass::deleteElement($user_id, $dislikes);
        } else {
            if (in_array($user_id, $likes)) {
                SupportClass::deleteElement($user_id, $likes);
                $this->setLikeUsers(SupportClass::to_pg_array($likes));
            }
            array_push($dislikes, $user_id);
        }
        $this->setDislikeUsers(SupportClass::to_pg_array($dislikes));
        $this->update();
    }

}
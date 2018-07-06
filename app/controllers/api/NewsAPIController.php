<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;

class NewsAPIController extends Controller
{
    public function getNewsAction()
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $news = News::find(["","order" => "News.date DESC"]);

            $listOfNews = [];

            $favCategories = Favoritecategories::findByUserId($userId);
            $favUsers = Favoriteusers::findByUserSubject($userId);

            foreach ($news as $new){
                if($new->getNewType() == 0){
                    //Информация о тендере
                    $tender = Auctions::findFirstByAuctionId($new->getIdentify());
                    $show = false;

                    foreach ($favCategories as $category)
                    {
                        if($category->getCategoryId() == $tender->tasks->getCategoryId()){
                            $show = true;
                            break;
                        }
                    }

                    if(!$show){
                        foreach($favUsers as $favUser){
                            if($favUser->getUserObject() == $tender->tasks->getUserId()){
                                $show = true;
                                break;
                            }
                        }
                    }

                    if($show) {
                        $user = Userinfo::findFirstByUserId($tender->tasks->getUserId());
                        $auctionId = $tender->getAuctionId();

                        $offer = Offers::findFirst("userId = '$userId' and auctionId = '$auctionId'");

                        if(!$offer)
                            $offer = null;

                        $auctionAndTask = ['tender' => $tender, 'tasks' => $tender->tasks, 'userinfo' => $user, 'offer' => $offer];
                        $listOfNews[] = ["news" => $new, "tender" => $auctionAndTask];
                    }
                } else if($new->getNewType() == 1){
                    //Информации о предложении
                    $offer = Offers::findFirstByOfferId($new->getIdentify());
                    $show = false;

                    foreach($favUsers as $favUser){
                        if($favUser->getUserObject() == $offer->getUserId()){
                            $show = true;
                            break;
                        }
                    }

                    if($show){
                        $auction = $offer->Auctions;
                        $task = $offer->auctions->tasks;
                        $userinfo = $task->Users->userinfo;

                        $offerWithTask = ['Offer' => $offer,'Tasks' => $task, 'Userinfo' => $userinfo, 'Tender'=> $auction];
                        $listOfNews[] = ["news" => $new, "offer" => $offerWithTask];
                    }

                } else if($new->getNewType() == 2){
                    //Информация об отзыве
                    $review = Reviews::findFirstByIdReview($new->getIdentify());
                    $show = false;

                    foreach($favUsers as $favUser){
                        if($favUser->getUserObject() == $review->getUserIdObject()){
                            $show = true;
                            break;
                        }
                    }

                    if($show){
                        $userinfo = Userinfo::findFirstByUserId($review->getUserIdSubject());

                        $reviewAndUserinfo = ['reviews' => $review,'userinfo' => $userinfo];
                        $listOfNews[] = ["news" => $new, "review" => $reviewAndUserinfo];
                    }
                }
            }



            return json_encode($listOfNews);

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}

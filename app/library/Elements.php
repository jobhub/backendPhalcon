<?php

use Phalcon\Mvc\User\Component;

/**
 * Elements
 *
 * Helps to build UI elements for the application
 */
class Elements extends Component
{
    private $_headerMenu = [
        'navbar-left' => [

            'userinfo' => [
                'caption' => 'Профиль',
                'action' => 'index'
            ],
            'tasks' => [
                'caption' => 'Задания',
                'action' => 'mytasks/'
            ],
            'auctions' => [
                'caption' => 'Тендеры',
                'action' => 'index'
            ],
        ],
        'navbar-right' => [
            'users' => [
                'caption' => 'Модерация',
                'action' => 'index'
            ],
            'register' => [
                'caption' => 'Зарегистрироваться',
                'action' => 'index'
            ],
            'session' => [
                'caption' => 'Войти',
                'action' => 'index'
            ]
        ]
    ];

    private $_tabs = [
        'Пользователи' => [
            'controller' => 'users',
            'action' => 'index',
        ],
        'Задания' => [
            'controller' => 'tasksModer',
            'action' => 'index',
        ],
        'Тендеры' => [
            'controller' => 'auctionsModer',
            'action' => 'index',
        ],
        'Предложения' => [
            'controller' => 'offers',
            'action' => 'index',
        ],
        'Логи' => [
            'controller' => 'logs',
            'action' => 'index',
        ],
        'Категории' => [
            'controller' => 'categories',
            'action' => 'index',
        ],
        'Сообщения' => [
            'controller' => 'messages',
            'action' => 'index',
        ]
    ];

    /**
     * Builds header menu with left and right items
     *
     * @return string
     */
    public function getMenu()
    {
        $auth = $this->session->get('auth');
        if ($auth) {
            $this->_headerMenu['navbar-right']['session'] = [
                'caption' => 'Выйти',
                'action' => 'end'
            ];

            $this->_headerMenu['navbar-left']['tasks']['action'].=$auth['id'];

            unset($this->_headerMenu['navbar-right']['register']);
            if($auth['role']!= "Moderator") {
                unset($this->_headerMenu['navbar-right']['users']);
            }
        } else {
            unset($this->_headerMenu['navbar-right']['users']);
            unset($this->_headerMenu['navbar-left']['userinfo']);
            unset($this->_headerMenu['navbar-left']['tasks']);
        }

        $controllerName = $this->view->getControllerName();
        foreach ($this->_headerMenu as $position => $menu) {
            echo '<div class="nav-collapse">';
            echo '<ul class="nav navbar-nav ', $position, '">';
            foreach ($menu as $controller => $option) {
                if ($controllerName == $controller) {
                    echo '<li class="active">';
                } else {
                    echo '<li>';
                }
                echo $this->tag->linkTo($controller . '/' . $option['action'], $option['caption']);
                echo '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }

    }

    /**
     * Returns menu tabs
     */
    public function getTabs()
    {
        $controllerName = $this->view->getControllerName();
        $actionName = $this->view->getActionName();
        echo '<ul class="nav nav-tabs">';
        foreach ($this->_tabs as $caption => $option) {
            if ($option['controller'] == $controllerName && ($option['action'] == $actionName)) {
                echo '<li class="active">';
            } else {
                echo '<li>';
            }
            echo $this->tag->linkTo($option['controller'] . '/' . $option['action'], $caption), '</li>';
        }
        echo '</ul>';
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Services\AuditService;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    public function index(Request $request): void
    {
        $this->requireAuth();
        $userId = Session::userId();
        $page = (int)($request->query('page', 1));

        $result = NotificationService::getUserNotifications($userId, $page);

        $this->view('notifications.index', $result);
    }

    public function markAsRead(Request $request): void
    {
        $this->requireAuth();
        $id = (int)$request->param('id');
        $userId = Session::userId();

        NotificationService::markAsRead($id, $userId);

        if ($request->isAjax()) {
            $this->json(['success' => true]);
        }

        $this->redirect(APP_URL . '/notificaciones');
    }

    public function markAllAsRead(Request $request): void
    {
        $this->requireAuth();
        $userId = Session::userId();

        NotificationService::markAllAsRead($userId);

        if ($request->isAjax()) {
            $this->json(['success' => true]);
        }

        $this->redirect(APP_URL . '/notificaciones');
    }

    public function unreadCount(Request $request): void
    {
        $userId = Session::userId();
        $count = NotificationService::getUnreadCount($userId);
        $this->json(['count' => $count]);
    }

    public function latest(Request $request): void
    {
        $userId = Session::userId();
        $notifications = NotificationService::getLatest($userId);
        $unreadCount = NotificationService::getUnreadCount($userId);
        $this->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}

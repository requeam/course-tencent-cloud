<?php

namespace App\Services\Auth;

use App\Models\User as UserModel;
use App\Models\UserSession as UserSessionModel;
use App\Repos\UserSession as UserSessionRepo;
use App\Services\Auth as AuthService;
use App\Traits\Client as ClientTrait;

class Home extends AuthService
{

    use ClientTrait;

    public function saveAuthInfo(UserModel $user)
    {
        $sessionId = $this->session->getId();

        $lifetime = $this->getSessionLifetime();

        /**
         * demo版本不限制多人登录
         */
        // $this->logoutOtherClients($user->id);

        $this->createUserSession($user->id, $sessionId, $lifetime);

        $authKey = $this->getAuthKey();

        $authInfo = [
            'id' => $user->id,
            'name' => $user->name,
        ];

        $this->session->set($authKey, $authInfo);
    }

    public function clearAuthInfo()
    {
        $authKey = $this->getAuthKey();

        $this->session->remove($authKey);
    }

    public function getAuthInfo()
    {
        $authKey = $this->getAuthKey();

        $authInfo = $this->session->get($authKey);

        return $authInfo ?: null;
    }

    public function getAuthKey()
    {
        return 'home_auth_info';
    }

    protected function createUserSession($userId, $sessionId, $lifetime)
    {
        $userSession = new UserSessionModel();

        $userSession->user_id = $userId;
        $userSession->session_id = $sessionId;
        $userSession->client_type = $this->getClientType();
        $userSession->client_ip = $this->getClientIp();
        $userSession->expire_time = time() + $lifetime;

        $userSession->create();
    }

    protected function logoutOtherClients($userId)
    {
        $cache = $this->getCache();

        $repo = new UserSessionRepo();

        $records = $repo->findByUserId($userId);

        if ($records->count() == 0) return;

        foreach ($records as $record) {
            $record->delete();
            $key = $this->getSessionCacheKey($record->session_id);
            $cache->delete($key);
        }
    }

    protected function getSessionLifetime()
    {
        $config = $this->getConfig();

        return $config->path('session.lifetime') ?: 24 * 3600;
    }

    protected function getSessionCacheKey($sessionId)
    {
        return "_PHCR_SESSION_:{$sessionId}";
    }

}

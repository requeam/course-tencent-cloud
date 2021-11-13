<?php
/**
 * @copyright Copyright (c) 2021 深圳市酷瓜软件有限公司
 * @license https://opensource.org/licenses/GPL-2.0
 * @link https://www.koogua.com
 */

namespace App\Http\Admin\Services;

use App\Exceptions\BadRequest as BadRequestException;
use App\Library\AppInfo as AppInfo;
use App\Models\Setting as SettingModel;
use App\Repos\Setting as SettingRepo;
use GuzzleHttp\Client as HttpClient;

class License extends Service
{

    public function saveLicence()
    {
        $content = $this->request->getPost('content', ['trim', 'string']);

        $this->checkLicense($content);

        $section = 'site';
        $itemKey = 'license';

        $settingRepo = new SettingRepo();

        $setting = $settingRepo->findItem($section, $itemKey);

        if ($setting) {

            $setting->item_value = $content;

            $setting->update();

        } else {

            $setting = new SettingModel();

            $setting->section = $section;
            $setting->item_key = $itemKey;
            $setting->item_value = $content;

            $setting->create();
        }

        $cache = $this->getCache();

        $cache->save('_APP_LICENSE_', $content);
    }

    protected function checkLicense($content)
    {
        $url = 'https://koogua.com/api/license/check';
        $url = 'http://portal.local/api/license/check';

        $serverHost = $this->request->getHttpHost();
        $serverIp = $this->request->getServerAddress();

        $appInfo = new AppInfo();

        $params = [
            'content' => $content,
            'server_host' => $serverHost,
            'server_ip' => $serverIp,
            'app_name' => $appInfo->get('name'),
            'app_alias' => $appInfo->get('alias'),
            'app_version' => $appInfo->get('version'),
        ];

        $client = new HttpClient();

        $response = $client->request('POST', $url, [
            'form_params' => $params,
            'http_errors' => false,
        ]);

        $content = json_decode($response->getBody(), true);

        if ($content['code'] !== 0) {
            throw new BadRequestException($content['msg']);
        }
    }

}

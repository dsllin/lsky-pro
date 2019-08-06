<?php
/**
 * Created by WispX.
 * User: WispX <wisp-x@qq.com>
 * Date: 2019-08-06
 * Time: 15:51
 * Link: https://github.com/wisp-x
 */

namespace app\index\controller\admin;

use think\Controller;
use think\Db;
use think\facade\Env;

class Update extends Base
{
    public function index()
    {
        // https://dev.tencent.com/u/wispx/p/lsky-pro-releases/git/raw/master/releases/lsky-pro-1.5.4.zip
        echo <<<EOT
<style>
  body {background-color: black;}
  span {color: white;}
</style>
<pre>
EOT;
        Db::startTrans();
        try {
            $this->out('检测更新中...');
            $version = $this->checkUpdate();
            if (! $version) {
                throw new \Exception('版本信息获取失败!');
            }
            $this->out("检测到新版本 v{$version}");
            $runtimePath = Env::get('runtime_path');
            $url = "https://dev.tencent.com/u/wispx/p/lsky-pro-releases/git/raw/master/releases/lsky-pro-{$version}.zip";
            $name = "lsky-pro-{$version}.zip";
            // $ip = $this->getRandIp();
            $context = stream_context_create(
                ['http' => [
                    'header' => "User-Agent: {$this->getRandUserAgent()}",
                    'timeout' => 600
                ]]
            );
            $this->out('安装包下载中, 请耐心等待...');
            if (!$file = @file_get_contents($url, false, $context)) {
                throw new \Exception('安装包下载失败, 请稍后再试!');
            }
            $this->out('安装包下载完成!', '正在保存安装包...');
            if (!@file_put_contents($runtimePath . $name, $file)) {
                throw new \Exception('安装包保存失败! 请检查 runtime 目录权限!');
            }
            $this->out('保存完成!', '正在执行解压...');
            // TODO 解压
            // TODO 执行更新sql
            // TODO 覆盖文件

            Db::commit();
            $this->out("<font color='green'>更新成功!</font>");
        } catch (\Exception $e) {
            Db::rollback();
            $this->out("<font color='red'>{$e->getMessage()}</font>");
        }

        ob_end_flush();
    }

    /**
     * 检测最新版
     *
     * @return bool|string
     * @throws \Exception
     */
    private function checkUpdate()
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->get('https://api.github.com/repos/wisp-x/lsky-pro/releases/latest');
        if (200 === $response->getStatusCode()) {
            $result = json_decode($response->getBody()->getContents());
            $version = ltrim(strtolower($result->name), 'v');
            if ($this->config['system_version'] > $version) {
                throw new \Exception('不可降级!');
            }
            if ($this->config['system_version'] == $version) {
                throw new \Exception('当前已经是最新版!');
            }

            return $version;
        }

        return false;
    }

    /**
     * Out Print
     *
     * @param mixed ...$args
     */
    private function out(...$args)
    {
        if (ob_get_level() == 0) ob_start();

        foreach ($args as $i => $arg) {
            echo "<span><font color='green'>root@lsky-pro</font>:~$ {$arg}</span>";
            echo str_pad('', 4096) . "\n";

            ob_flush();
            flush();
            sleep(1);
        }
    }

    /**
     *  获取随机 UserAgent
     *
     * @return mixed
     */
    private function getRandUserAgent()
    {
        $array = [
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/22.0.1207.1 Safari/537.1",
            "Mozilla/5.0 (X11; CrOS i686 2268.111.0) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1092.0 Safari/536.6",
            "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1090.0 Safari/536.6",
            "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/19.77.34.5 Safari/537.1",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.9 Safari/536.5",
            "Mozilla/5.0 (Windows NT 6.0) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.36 Safari/536.5",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
            "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_0) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1062.0 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1062.0 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.1 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.1 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.1 Safari/536.3",
            "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.0 Safari/536.3",
            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24",
            "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/535.24 (KHTML, like Gecko) Chrome/19.0.1055.1 Safari/535.24",
            "Mozilla/5.0 (Macintosh; U; Mac OS X Mach-O; en-US; rv:2.0a) Gecko/20040614 Firefox/3.0.0 ",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.5; en-US; rv:1.9.0.3) Gecko/2008092414 Firefox/3.0.3",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.5; en-US; rv:1.9.1) Gecko/20090624 Firefox/3.5",
            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2.14) Gecko/20110218 AlexaToolbar/alxf-2.0 Firefox/3.6.14",
            "Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10.5; en-US; rv:1.9.2.15) Gecko/20110303 Firefox/3.6.15",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.6; rv:2.0.1) Gecko/20100101 Firefox/4.0.1",
            "Opera/9.80 (Windows NT 6.1; U; en) Presto/2.8.131 Version/11.11",
            "Opera/9.80 (Android 2.3.4; Linux; Opera mobi/adr-1107051709; U; zh-cn) Presto/2.8.149 Version/11.10",
            "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/531.21.8 (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10",
            "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US) AppleWebKit/533.17.8 (KHTML, like Gecko) Version/5.0.1 Safari/533.17.8",
            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.19.4 (KHTML, like Gecko) Version/5.0.2 Safari/533.18.5",
            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0",
            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)",
            "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)",
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)"
        ];

        return $array[array_rand($array)];
    }

    /**
     * 随机IP
     *
     * @return string
     */
    private function getRandIp()
    {
        $array = ["218", "218", "66", "66", "218", "218", "60", "60", "202", "204", "66", "66", "66", "59", "61", "60", "222", "221", "66", "59", "60", "60", "66", "218", "218", "62", "63", "64", "66", "66", "122", "211"];
        $rand = mt_rand(0, count($array));
        $ip1id = $array[$rand];
        $ip2id = round(rand(600000, 2550000) / 10000);
        $ip3id = round(rand(600000, 2550000) / 10000);
        $ip4id = round(rand(600000, 2550000) / 10000);
        return $ip1id . "." . $ip2id . "." . $ip3id . "." . $ip4id;
    }
}

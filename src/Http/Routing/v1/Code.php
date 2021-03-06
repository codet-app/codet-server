<?php
/**
 * Http package
 * 
 * PHP version 7
 * 
 * @category PHP
 * @package  Codet
 * @author   Golodnyi <ochen@golodnyi.ru>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/codet-app/codet-server
 */
namespace Codet\Http\Routing;

/**
 * Auth class
 * 
 * PHP version 7
 * 
 * @category PHP
 * @package  Codet
 * @author   Golodnyi <ochen@golodnyi.ru>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/codet-app/codet-server
 */
class Code
{
    private $_storage;

    public function __destruct()
    {
    }
    /**
     * __construct
     * 
     * Set storage
     */
    public function __construct()
    {
        $this->_storage = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', '..', 'storage']);
    }

    /**
     * Add code
     *
     * @param [type] $req  request
     * @param [type] $resp response
     * @param [type] $vars uri segment vars
     * 
     * @return string
     */
    public function add($req, $resp, $vars): string
    {
        if ($req->server['request_method'] == 'OPTIONS') {
            $resp->status(200);
            return json_encode([]);
        }
        if (!is_writable($this->_storage)) {
            $resp->status(500);
            return json_encode(['result' => 'storage is not writable']);
        }

        $code = uniqid();
        $folder = $this->_storage . DIRECTORY_SEPARATOR . mb_substr($code, 0, 5);
        
        if (!is_dir($folder)) {
            if (!mkdir($folder)) {
                $req->status(500);
                return json_encode(['result' => 'cannot create folder']);
            }
        }

        if (!$data = json_decode($req->rawContent(), true)) {
            $req->status(500);
            return json_encode(['result' => 'body is not json']);
        }

        if (!isset($data['code']) || !isset($data['lang'])) {
            $req->status(204);
            return json_encode(['result' => 'no content']);
        }

        if (!empty(trim($data['pwd']))) {
            $data['pwd'] = password_hash(trim($data['pwd']), PASSWORD_DEFAULT);
        }

        if (mb_strlen($data['comment']) > 1024) {
            $data['comment'] = mb_substr($data['comment'], 0, 1024);
        }
        
        if (mb_strlen($data['code']) > 65535) {
            $data['code'] = mb_substr($data['code'], 0, 65535);
        }

        if (!file_put_contents($folder . DIRECTORY_SEPARATOR . mb_substr($code, 5, 8) . '.json', json_encode($data))) {
            $req->status(500);
            return json_encode(['result' => 'cannot create json file']);
        }

        return json_encode(['result' => $code]);
    }

    /**
     * Get code
     *
     * @param [type] $req  request
     * @param [type] $resp response
     * @param [type] $vars uri segment vars
     * 
     * @return string
     */
    public function get($req, $resp, $vars): string
    {
        $file = $this->_storage . DIRECTORY_SEPARATOR 
        . mb_substr($req->get['code'], 0, 5) 
        . DIRECTORY_SEPARATOR . mb_substr($req->get['code'], 5, 8) . '.json';

        if (!file_exists($file)) {
            $resp->status(404);
            return json_encode(['result' => 'code not found']);
        }

        $data = file_get_contents($file);

        if (!$data = json_decode($data)) {
            $resp->status(500);
            return json_encode(['result' => 'bad file']);
        }

        if (!empty($data->pwd) && !isset($req->get['pwd'])) {
            $resp->status(401);
            return json_encode(['result' => 'need password']);
        }

        if (!empty($data->pwd) && !password_verify(trim($req->get['pwd']), $data->pwd)) {
            $resp->status(403);
            return json_encode(['result' => 'incorrect password']);
        }

        unset($data->pwd);
        return json_encode(['result' => $data]);
    }
}

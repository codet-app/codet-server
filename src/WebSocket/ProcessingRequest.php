<?php
/**
 * WebSocket package
 * 
 * PHP version 7
 * 
 * @category PHP
 * @package  Codet
 * @author   Golodnyi <ochen@golodnyi.ru>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/codet-app/codet-server
 */
namespace Codet\WebSocket;

use Codet\WebSocket\Classes\Chat;
/**
 * Processing Request
 * 
 * PHP version 7
 * 
 * @category PHP
 * @package  Codet
 * @author   Golodnyi <ochen@golodnyi.ru>
 * @license  https://opensource.org/licenses/MIT MIT
 * @link     https://github.com/codet-app/codet-server
 */
class ProcessingRequest
{
    /**
     * __construct
     *
     * @param [type] $server server
     * @param [type] $frame  frame
     */
    public function __construct($server, $frame)
    {
        $chat = new Chat($server, $frame);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: tsuneko
 * Date: 02.04.18
 * Time: 15:51
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\Classes\Socket\ChatSocket;

class ChatServer extends Command
{
    protected $signature = 'chat_server:serve';

    protected $description = 'Command description';

    public function __constuct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info("Start serve");

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new ChatSocket()
                )
            ),
            8080
        );

        $server->run();
    }

}
<?php
/**
 * Created by PhpStorm.
 * User: tsuneko
 * Date: 02.04.18
 * Time: 15:11
 */

namespace App\Classes\Socket;

use App\Classes\Socket\BaseSocket;
use Ratchet\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ChatSocket extends BaseSocket
{
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {

        //get user`s token from current session
        $t = $conn->httpRequest->getUri()->getQuery();
        //change this user`s status login
        DB::table('users')
            ->where('remember_token',$t)
            ->update(['islogin' => 'true']);

        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";

        //dump($conn);


    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            //send to each client connected
            $client->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        //get user`s token from current session
        $t = $conn->httpRequest->getUri()->getQuery();

        //disconnect user
        DB::table('users')
            ->where('remember_token',$t)
            ->update(['islogin' => 'false']);

        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: tsuneko
 * Date: 02.04.18
 * Time: 15:11
 */

namespace App\Classes\Socket;

use App\Classes\Socket\BaseSocket;
use App\Classes\User;
use Illuminate\Support\Facades\Log;
use Ratchet\ConnectionInterface;

class ChatSocket extends BaseSocket
{
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {

        // get user`s token from current session
        $t = $conn->httpRequest->getUri()->getQuery();
        $user = User::where(['token' => $t])->first();
        if (!$user || $user->isbaned == "1") {
            $conn->close();
        }
        $conn->user = $user;

        $this->clients->attach($conn);

        foreach ($this->clients as $client) {
            $names[] = $client->user->name;
        }
        $this->sendAll([
            'type' => 'userlist',
            'names' => $names,
        ]);

        $this->sendAll([
            'type' => 'online',
            'name' => $conn->user->name,
        ]);

        if ($user->admin === "1") {
            $conn->send(json_encode([
                'type' => 'users',
                'names' => User::all('name', 'id'),
            ]));
        }

        echo "{$conn->user->name } connected! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $data = json_decode($msg, true);

        if (!isset($data['type'])) {
            return;
        }

        switch ($data['type']) {
            case 'message':
                if ($from->user->ismuted == "1") {
                    break;
                } else {
                    $this->sendAll([
                        'type' => 'message',
                        'name' => $from->user->name,
                        'text' => $data['text'],
                    ]);
                    break;
                }
            case 'mute':
                if ($from->user->admin === "1"){
                    $this->sendAll([
                        'type' => 'mute',
                        'name' => $data['name'],
                        'id' => $data['id'],
                    ]);
                    User::where('id', $data['id'])->update(['ismuted'=>"1"]);
                }
                break;

            case 'ban':
                if ($from->user->admin === "1"){
                    $this->sendAll([
                        'type' => 'ban',
                        'name' => $data['name'],
                        'id' => $data['id'],
                        ]);
                User::where('id', $data['id'])->update(['isbaned'=>"1"]);
                }
                break;
        }

    }

    public function onClose(ConnectionInterface $conn) {

        $this->clients->detach($conn);

        $this->sendAll([
            'type' => 'offline',
            'name' => $conn->user->name,
        ]);

        foreach ($this->clients as $client) {
            $names[] = $client->user->name;
        }
        $this->sendAll([
            'type' => 'userlist',
            'names' => $names,
        ]);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    protected function sendAll($data){
        foreach ($this->clients as $client) {
            $client->send(json_encode($data));
        }
    }





//    protected $clients;
//
//    public function __construct()
//    {
//        $this->clients = new \SplObjectStorage;
//    }
//
//    public function onOpen(ConnectionInterface $conn)
//    {
//
//        // get user`s token from current session
//        $t = $conn->httpRequest->getUri()->getQuery();
//        $user = User::where(['token' => $t])->first();
//        if (!$user || $user->isbaned === 'true') {
//            $conn->close();
//        }
//        $conn->user = $user;
//        $this->clients->attach($conn);
//
//
//        //get all users online and send userlist
//        foreach ($this->clients as $client) {
//            $names[] = $client->user->name;
//        }
//        $this->sendAll([
//                'type' => 'userlist',
//                'names' => $names,
//        ]);
//
//
//        //block button if user is muted
//        if ($user->ismuted === "true"){
//            $conn->send(json_encode([
//                'type'=>'stillMuted',
//            ]));
//        }
//
//
//        // send to admin all users
//        if ($user->type === 'admin') {
//            $conn->send(json_encode([
//                'type' => 'allusers',
//                'users' => User::all('name','id'),
//            ]));
//        }
//
//        // send message into chat when user online
//        $this->sendAll([
//
//            'type' => 'online_into_chat',
//            'name' => $conn->user->name,
//        ]);
//
//        echo "{$conn->user->name } connected! ({$conn->resourceId})\n";
//    }
//
//    public function onMessage(ConnectionInterface $from, $msg)
//    {
//        $numRecv = count($this->clients) - 1;
//        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
//            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');
//
//        $data = json_decode($msg, true);
//
//        if (!isset($data['type'])) {
//            return;
//        }
//
//        switch ($data['type']) {
//
//            //send text message to each client connected
//            case 'message':
//                if ($from->user->ismuted === 'true') {
//                    break;
//                } else {
//                    $this->sendAll([
//                            'type' => 'message',
//                            'user' => $from->user->name,
//                            'text' => $data['text'],
//                        ]);
//                    break;
//                }
//
//            // send message when user online
//            case 'online_into_chat':
//                $this->sendAll([
//                        'type' => 'online_into_chat',
//                        'name' => $from->user->name,
//                    ]);
//                break;
//
//            case 'mute':
//                if ($from->user->type === 'admin'){
//                    $this->sendAll([
//                        'type' => 'mute',
//                        'name' => $data['user'],
//                        'id' => $data['id'],
//                    ]);
//                    User::where('id', $data['id'])->update(['ismuted'=>'true']);
//                }
//                break;
//
//            case 'ban':
//                if ($from->user->type === 'admin'){
//                    $this->sendAll([
//                        'type' => 'ban',
//                        'name' => $data['user'],
//                        'id' => $data['id'],
//                        ]);
//                User::where('id', $data['id'])->update(['isbaned'=>'true']);
//                }
//                break;
//        }
//    }
//
//    public function onClose(ConnectionInterface $conn)
//    {
//        $this->clients->detach($conn);
//
//        // send message into chat when user offline
//        $this->sendAll([
//                'type' => 'offline_into_chat',
//                'name' => $conn->user->name,
//            ]);
//
//        //get all users online and send userlist
//        foreach ($this->clients as $client) {
//            $names[] = $client->user->name;
//        }
//        $this->sendAll([
//                'type' => 'userlist',
//                'names' => $names,
//            ]);
//
//        echo "Connection {$conn->resourceId} has disconnected\n";
//    }
//
//    public function onError(ConnectionInterface $conn, \Exception $e)
//    {
//        echo "An error has occurred: {$e->getMessage()}\n";
//
//        $conn->close();
//    }
//
//    protected function sendAll($data){
//        foreach ($this->clients as $client) {
//            $client->send(json_encode($data));
//        }
//    }
}
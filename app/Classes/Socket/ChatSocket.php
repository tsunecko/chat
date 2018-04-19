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
                $user = User::where('id', $data['id']);
                if ($from->user->admin === "1"){
                    if($user->value('ismuted')) {
                        $type = 'unmute';
                        $user->update(['ismuted'=>"0"]);
                    } else {
                        $type = 'mute';
                        $user->update(['ismuted'=>"1"]);
                    }
                    $this->sendAll([
                        'type' => $type,
                        'name' => $user->value('name'),
                    ]);
                }
                break;

            case 'ban':
                $user = User::where('id', $data['id']);
                if ($from->user->admin === "1"){
                    if($user->value('isbaned')) {
                        $type = 'unban';
                        $user->update(['isbaned'=>"0"]);
                    } else {
                        $type = 'ban';
                        $user->update(['isbaned'=>"1"]);
                    }
                    $this->sendAll([
                        'type' => $type,
                        'name' => $user->value('name'),
                    ]);
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
}
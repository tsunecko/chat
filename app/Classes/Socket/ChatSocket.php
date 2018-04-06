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
use Ratchet\ConnectionInterface;

class ChatSocket extends BaseSocket
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {

        // get user`s token from current session
        $t = $conn->httpRequest->getUri()->getQuery();

        // find user in db and check banned status
        $user = User::where(['remember_token' => $t])->first();
        if (!$user || $user->isbanned) {
            $conn->close();
        }

        // store current user in current connection
        $conn->user = $user;

        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        //get all users online
        foreach ($this->clients as $client) {
            $names[] = $client->user->name;
        }

        // send to new user current user list
        foreach ($this->clients as $client) {
            $client->send(json_encode([
                'type' => 'userlist',
                'list' => [
                    'names' => $names,
                ],
            ]));
        }

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $data = json_decode($msg, true);

        if (!isset($data['type'])) {
            return;
        }

        switch ($data['type']) {

            //send text message to each client connected
            case 'message':
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        'type' => 'message',
                        'user' => $data['user'],
                        'text' => $data['text'],
//                        'hour' => $data['hour'],
//                        'min' => $data['min'],
                    ]));
                }
                break;

            // send message when user online
            case 'online_into_chat':
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        'type' => 'online_into_chat',
                        'islogin' => $data['islogin'],
                    ]));
                }
                break;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {

        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        // send message into chat when user offline
        foreach ($this->clients as $client) {
            $client->send(json_encode([
                'type' => 'offline_into_chat',
                'islogout' => $conn->user->name,
            ]));
        }

        //get all users online
        foreach ($this->clients as $client) {
            $names[] = $client->user->name;
        }

        // send to new user current user list
        foreach ($this->clients as $client) {
            $client->send(json_encode([
                'type' => 'userlist',
                'list' => [
                    'names' => $names,
                ],
            ]));
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
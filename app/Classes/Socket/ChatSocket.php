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
use Illuminate\Support\Facades\DB;

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

        $user = User::where(['token' => $t])->first();
        if (!$user || $user->isbaned === 'true') {
            $conn->close();
        }

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

        // send to admin all users

        $users = DB::table('users')
            ->select('name','token')
            ->paginate();
        //dump($users);

        foreach ($this->clients as $client) {
            if ($client->user->type === 'admin') {
                $client->send(json_encode([
                    'type' => 'allusers',
                    'users' => $users,
                ]));
            }
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
        dump($data['type']);

        switch ($data['type']) {

            //send text message to each client connected
            case 'message':
                $token = $data['token'];
                $user = User::where(['token' => $token])->first();

                if ($user->ismuted === 'true') {
                    break;
                } else {
                    foreach ($this->clients as $client) {
                        $client->send(json_encode([
                            'type' => 'message',
                            'user' => $data['user'],
                            'text' => $data['text'],
                            'token' => $data['token'],
                        ]));
                    }
                    break;
                }

            // send message when user online
            case 'online_into_chat':
                foreach ($this->clients as $client) {

                    $client->send(json_encode([
                        'type' => 'online_into_chat',
                        'islogin' => $data['islogin'],
                    ]));

                    if ($client->user->ismuted === 'true') {
                        $client->send(json_encode([
                                'type' => 'stillMuted',
                            ]
                        ));
                    }
                }
                break;

            case 'mute':
                $token = $data['token'];
                $user = User::where(['token' => $token]);

                //send message into chat
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        'type' => 'mute',
                        'name' => $data['user'],
                        'token' => $data['token'],
                    ]));
                }
                $user->update(['ismuted'=>'true']);
                break;

            case 'ban':
                $token = $data['token'];
                $user = User::where(['token' => $token]);

                //send message into chat
                foreach ($this->clients as $client) {
                    $client->send(json_encode([
                        'type' => 'ban',
                        'name' => $data['user'],
                        'token' => $data['token'],
                    ]));
                }
                $user->update(['isbaned'=>'true']);
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
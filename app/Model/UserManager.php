<?php

namespace App\Model;

use mysql_xdevapi\Result;
use Nette\Application\UI\Presenter;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Traversable;
use Nette\Neon\Exception;
use Nette\Database\IRow;
use Nette\Security\User;

class UserManager
{

    /**
     * @var Context
     */
    private $database;

    /**
     * PostManager constructor.
     * @param Context $database
     */
    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param int $user_id
     * @return ActiveRow|null
     */
    public function getLoggedUserData(int $user_id): ?ActiveRow
    {
        return $this->database->table('users')->get($user_id);
    }

    /**
     * @param string $username
     * @return mixed
     */
    public function getUserDataByUsername(string $username)
    {
        return $this->database->table('users')
            ->select('*')
            ->where('username = ?', $username)
            ->fetch();
    }

    /**
     * @param string|null $username
     * @return array|null
     */
    public function getUserIdByUsername(string $username = null):?array
    {
        if($username == null) return null;
        return $this->database->table('users')
            ->select('user_id')
            ->where('username = ?', $username)
            ->fetchAll();
    }

    /**
     * @param int $limit
     * @param int $user_id
     * @return IRow[]|null
     */
    public function getRandomUsers(int $limit = 10, int $user_id)
    {
        $query = "
            SELECT u.lastname,
                   u.firstname,
                   u.username,
                   u.user_profile_img_path,
                   u.user_id,
                   f.state AS friendship,
                   f.user_receive_id,
                   f.user_sent_id,
                   f.friendship_id
            FROM users u
            LEFT JOIN friendship f ON 
                user_receive_id = u.user_id AND user_sent_id = $user_id
                OR
                user_sent_id = u.user_id AND user_receive_id = $user_id
            WHERE
                    u.username IS NOT NULL 
                AND
                    u.user_id != ?
                AND
                    f.state IS NULL
            ORDER BY RAND()
            LIMIT $limit
            ";

        $randomUsers = $this->database->query($query, $user_id)->fetchAll();

        bdump($randomUsers,'get random users');

        if (!$randomUsers) return null;

        return $randomUsers;
    }

    /**
     * @param int $user_id
     * @return array|null
     */
    public function getFriendRequestState(int $random_user_id, int $user_id)
    {
//        $state = $this->database->table('friendship')
//            ->select('state, friendship_id, user_sent_id, user_receive_id')
//            ->where('user_receive_id = ? OR user_sent_id = ?', $random_user_id, $random_user_id)
//            ->fetch();

        $state = $this->database->query("
        SELECT state, friendship_id, user_sent_id, user_receive_id
        FROM friendship
        WHERE
        CASE
            WHEN user_receive_id = $random_user_id
            THEN user_receive_id = $random_user_id AND user_sent_id = $user_id
            WHEN user_sent_id = $random_user_id
            THEN user_sent_id = $random_user_id AND user_receive_id = $user_id
        END
        
        
        ")->fetch();

        if (!$state) {
            return null;
        }
        return $state;
    }


    /**
     * @param string $recipient
     * @param int $loggedUserID
     * @return array|bool|int|iterable|ActiveRow|Selection|Traversable
     * @throws Exception
     */
    public function insertFriendRequest(string $recipient, int $loggedUserID)
    {
        $recipientID = $this->database->table('users')
            ->where('username = ?', $recipient)
            ->select('user_id')
            ->fetch();

        $friendship = $this->database->table('friendship')
            ->where('user_sent_id = ? AND user_receive_id = ? OR user_sent_id = ? AND user_receive_id = ?',
                $loggedUserID, $recipientID['user_id'], $recipientID['user_id'], $loggedUserID)
            ->fetch();

        if ($friendship) {
            throw new Exception("Request was already sent");
        }

        if (!$recipientID) {
            throw new Exception('User does not exist');
        }

        $data = array([
            'user_sent_id' => $loggedUserID,
            'user_receive_id' => $recipientID['user_id'],
            'state' => 'pending'
        ]);

        return $this->database->table('friendship')->insert($data);
    }

    /**
     * @param int $request_id
     * @throws Exception
     */
    public function deleteFriendRequest(int $request_id)
    {
        $request = $this->database->table('friendship')->get($request_id);

        if(!$request){
            throw new Exception('Friend request does not exist');
        }

        $request->delete();
    }

    /**
     * @param int $request_id
     * @param string $state
     * @throws Exception
     */
    public function updateFriendRequestStatus(int $request_id, string $state)
    {
        $request = $this->database->table('friendship')->get($request_id);

        if(!$request){
            throw new Exception('Friend request does not exist', 'FRIEND_REQUEST_DONT_EXIST');
        }

        $data = array(
            'state' => $state
        );

        $request->update($data);
    }

    /**
     * @param int $user_id
     * @return array
     */
    public function getFriendRequestsForUser(int $user_id)
    {
        return ([
            'userSentFriendRequests' => $this->getUserSentFriendRequests($user_id),
            'userReceiveFriendRequests' => $this->getUserReceiveFriendRequests($user_id)
        ]);
    }

    /**
     * @param int $user_id
     * @return IRow[]
     */
    public function getUserSentFriendRequests(int $user_id)
    {
        $query = "
            SELECT 
                friendship_id,
                user_sent_id,
                user_receive_id,
                state,
                created_at,
                username,
                user_profile_img_path,
                firstname,
                lastname,
                user_id
            FROM 
                friendship
            LEFT JOIN users ON user_sent_id = user_id
            WHERE
                user_receive_id = ?
              AND
                state = 'pending'
            ";

        return $this->database->query($query, $user_id)->fetchAll();
    }

    /**
     * @param int $user_id
     * @return IRow[]
     */
    public function getUserReceiveFriendRequests(int $user_id)
    {
        $query = "
            SELECT 
                friendship_id,
                user_sent_id,
                user_receive_id,
                state,
                created_at,
                username,
                user_profile_img_path,
                firstname,
                lastname,
                user_id
            FROM 
                friendship
            LEFT JOIN users ON user_receive_id = user_id
            WHERE
                user_sent_id = ?
            AND
                state = 'pending'
            ";

        return $this->database->query($query, $user_id)->fetchAll();
    }

    /**
     * @param $user_id
     * @return IRow[]
     */
    public function getUserFriends($user_id)
    {
        $query = "
            SELECT 
                f.friendship_id,
                f.user_sent_id,
                f.user_receive_id,
                f.state,
                f.created_at,
                u.username,
                u.user_profile_img_path,
                u.firstname,
                u.lastname,
                u.user_id
            FROM 
                friendship f, users u
            WHERE
                CASE
                    WHEN f.user_receive_id = ?
                    THEN f.user_sent_id = u.user_id
                    WHEN f.user_sent_id = ?
                    THEN f.user_receive_id = u.user_id
                END
            AND
                state = 'accepted'
            ";

        return $this->database->query($query, $user_id, $user_id)->fetchAll();
    }

}
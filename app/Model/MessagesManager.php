<?php

namespace App\Model;

use Nette;

class MessagesManager{

    /**
     * @var Nette\Database\Context
     */
    private $database;

    /**
     * PostManager constructor.
     * @param Nette\Database\Context $database
     */
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param int $owner_id
     * @param $to_user_id
     * @return Nette\Database\IRow[]
     */
    public function userCreatedInbox(int $owner_id, $to_user_id)
    {
        $query = "
            SELECT inbox_hash FROM inboxes
            WHERE
            CASE
                WHEN user_id = $owner_id
                THEN user_id = $owner_id AND to_user_id = $to_user_id
            END
        ";
        $hash = $this->database->query($query)->fetchAll();
        if($hash){
            $hash = $hash[0]['inbox_hash'];
        }
        return $hash;
    }

    /**
     * @param int $owner_id
     * @param $to_user_id
     * @return Nette\Database\IRow[]
     */
    public function InboxWasCreatedForUser(int $owner_id, $to_user_id)
    {
        $query = "
            SELECT inbox_hash FROM inboxes
            WHERE
            CASE
                WHEN to_user_id = $owner_id
                THEN to_user_id = $owner_id AND user_id = $to_user_id
            END
        ";
        $hash = $this->database->query($query)->fetchAll();
        if($hash){
            $hash = $hash[0]['inbox_hash'];
        }
        return $hash;
    }

    /**
     * @param int $owner_id
     * @param $to_user_id
     * @return string
     * @throws \Exception
     */
    public function createOwnerInbox(int $owner_id, $to_user_id)
    {
        $hash = $this->userCreatedInbox($owner_id, $to_user_id);

        if(!$hash){

            if($this->InboxWasCreatedForUser($owner_id, $to_user_id))$hash = $this->InboxWasCreatedForUser($owner_id, $to_user_id);
            else $hash = bin2hex(random_bytes(16));

            $data = [
                'user_id' => $owner_id,
                'sender_id' => $owner_id,
                'inbox_hash' => $hash,
                'last_message' => null,
                'seen' => null,
                'unseen_number' => null,
                'deleted' => false,
                'to_user_id' => $to_user_id
            ];
            $this->database->table('inboxes')->insert($data);
        }

        return $hash;
    }


    /**
     * @param int $owner_id
     * @param $to_user_id
     * @param $data
     * @throws \Exception
     */
    public function updateInbox(int $owner_id, $to_user_id, $data)
    {
        $hash = $this->createOwnerInbox($owner_id, $to_user_id);
        bdump($hash);
        $this->database->table('inboxes')->where('inbox_hash', $hash)->update($data);

    }

    /**
     * @param int $user_id
     * @return Nette\Database\IRow[]|null
     */
    public function getInboxes(int $user_id)
    {
        $query = "
            SELECT
            i.inbox_id,
            i.user_id AS inbox_user_id,
            i.sender_id,
            i.inbox_hash,
            i.last_message,
            i.seen,
            i.unseen_number,
            i.deleted,
            i.to_user_id,
            u.user_id,
            u.firstname,
            u.lastname,
            u.username,
            u.user_profile_img_path
            FROM inboxes i
            JOIN users u on u.user_id = i.to_user_id
            WHERE i.user_id = ?
        ";
        $inboxes = $this->database->query($query, $user_id)->fetchAll();

        if(!$inboxes){
            return null;
        }

        return $inboxes;
    }

    /**
     * @param $inbox_hash
     * @return Nette\Database\IRow[]|null
     */
    public function getMessagesByInboxHash($inbox_hash)
    {
        $query = "
            SELECT
            m.*,
            user_profile_img_path,
            firstname,
            lastname,
            username
            FROM messages m 
            JOIN users u ON m.sender_id = u.user_id
            WHERE inbox_hash = ?
            ORDER BY sent_time DESC
        ";
        $messages = $this->database->query($query, $inbox_hash)->fetchAll();

        if(!$messages) return null;

        return $messages;
    }


}

<?php

class User 
{
    public string $ID;
    public string $Password;
    public string $Email;
    public bool $Admin;
    public function __construct(
        string $id,
        string $hash,
        string $email,
        bool $is_admin = false
    )
    {
        if (isEmpty($id))
        {
            throw new InvalidArgumentException("id was null!");
        }
        if (isEmpty($hash))
        {
            throw new InvalidArgumentException("hash was null!");
        }
        $this->ID = $id;
        $this->Password = $hash;
        $this->Email = $email;
        $this->Admin = $is_admin;
    }
    public function checkPassword(string $plain_text_password) : bool
    {
        if (isEmpty($this->ID) || isEmpty($this->Password) || isEmpty($plain_text_password))
        {
            return false;
        }
        return password_verify($plain_text_password, $this->Password);
    }
    public static function Load(mysqli $db, string $id): User|null
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!");
        }
        if (isEmpty($id))
        {
            return null;
        }
        $query = "SELECT * FROM `users` WHERE `ID` = ?";
        $result = $db->execute_query($query, array($id));
        if (!$result || $result->num_rows === 0)
        {
            return null;
        }
        $row = $result->fetch_assoc();
        if (!$row)
        {
            return null;
        }
        return new User(
            $row['ID'],
            $row['Password'],
            $row['Email'],
            (bool)$row['Admin']
        );
    }
    public static function Create(mysqli $db, string $id, string $plain_text_password, string $email, bool $is_admin = false) : bool
    {
        if (!isset($db) || !($db instanceof mysqli))
        {
            throw new InvalidArgumentException("db was not a mysqli object!");
        }
        if (isEmpty($plain_text_password))
        {
            throw new InvalidArgumentException("password can't be null!");
        }
        $hash = password_hash($plain_text_password, PASSWORD_BCRYPT);
        $user = new User($id, $hash, $email, $is_admin);
        return $user->Upload($db);
    }
    private function Upload(mysqli $db) : bool
    {
        $query = "INSERT INTO `users` (`ID`, `Password`, `Email`, `Admin`) VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        if (!$stmt || !$stmt->bind_param('sssi', $this->ID, $this->Password, $this->Email, $is_admin))
        {
            return false;
        }
        $is_admin = $this->Admin ? 1 : 0;
        return (bool)$stmt->execute();
    }
}
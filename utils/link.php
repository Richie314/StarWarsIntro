<?php

class Link
{
    public string $Url;
    public string $Img;
    public string $Text;
    public function __construct($url, $img, $text)
    {
        $this->Url = $url;
        $this->Img = $img;
        $this->Text = htmlspecialchars($text);
    }
}
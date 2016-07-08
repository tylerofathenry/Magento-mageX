<?php

class My_Class_Name_Is_Here extends Mage_HTTP_Client_Curl
{
    public function killAndOutput($text = "")
    {
        die($text);
    }
}
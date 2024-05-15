<?php
namespace Bibliotek\Entity;
use DateTime;
use Exception;

class uOpenLibraryAPI{
    
    static $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Content-type: application/json;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"run\""
        ],
    );

    static function searchByISBN(int $_ISBN) : array {
        $url ="https://openlibrary.org/api/books?bibkeys=ISBN:".$_ISBN."&jscmd=details&format=json";
        OpenLibraryAPI::$options[CURLOPT_URL] = $url;
        $curl = curl_init();
        curl_setopt_array($curl, OpenLibraryAPI::$options);
        $result = curl_exec($curl);
        $err = curl_error($curl);
        $book = json_decode($result, true);
        curl_close($curl);
        if ($err) {
            throw new Exception("Error: the connection to APIs  went wrong. Code " . $err);
        } 
        else {
            var_dump($book); //for debugging only!
            // set_error_handler(function($errno, $errstr, $errfile, $errline) {
            //     // error was suppressed with the @-operator
            //     if (0 === error_reporting()) {
            //         return false;
            //     }
                
            //     throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            // });
            // ............code from where catch the warnings...........
            // restore_error_handler();

            $publishYear = $book["ISBN:".$_ISBN]["details"]["publish_date"];
            $bookData = array(
                'title'       => $book["ISBN:".$_ISBN]["details"]["title"],
                'ISBN'        => $_ISBN,
                'publishYear' => new DateTime("$publishYear-01-01"),
                'authors'     => array(),
                'genres'      => $book["ISBN:".$_ISBN]["details"]["subjects"],
                'description' => $book["ISBN:".$_ISBN]["details"]["description"]["value"],
                'quantity'    => 0,
                'pagesNumber' => (int)$book["ISBN:".$_ISBN]["details"]["pagination"]
            );
            foreach ($book["ISBN:".$_ISBN]["details"]["authors"] as $author){
                array_push($bookData['authors'], $author["name"]);
            }

            return $bookData;
        }
    }
}
?>
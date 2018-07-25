<?php

function moments($seconds)
{
    if($seconds < 60 * 60 * 24 * 30)
    {
        return "within the month";
    }

    return "a while ago";
}

function getPosts($link)
{
    $posts = [];

    $lines = mysqli_query($link, 'SELECT * FROM posts');

    if (mysqli_num_rows($lines) > 0) 
    {
        $importantPriority  = [];
        $highPriority       = [];
        $normalPriority     = [];

        while($row = mysqli_fetch_array($lines, MYSQLI_ASSOC)) {

            $post = validatePost($row);

                if($post != false)
                {
                    switch($post['priority'])
                    {
                        case 3;
                            $normalPriority[] = $post;
                            break;
                        case 2;
                            $highPriority[] = $post;
                            break;
                        case 1;
                            $importantPriority[] = $post;
                            break;
                    }
                }
        } //  while($row = mysqli_fetch_assoc($lines)) {
    } else {
        echo "0 results";
    } // if (mysqli_num_rows($lines) > 0)


        $posts = array_merge($importantPriority, $highPriority, $normalPriority);
    // } // if(file_exists("posts.txt"))
    // mysqlconnClose($link);

    return $posts;
}

function searchPosts($term, $link)
{
    $posts = [];

    $lines = mysqli_query($link, "SELECT * FROM posts WHERE comment LIKE '%$term%'");

    /*
    if ($lines = mysqli_query($link, "SELECT * FROM posts WHERE comment LIKE '%$term%'")
    {
        printf("Select returned %d rows.\n<br />", mysqli_num_rows($lines));
    }
    */

    if (mysqli_num_rows($lines) > 0) 
    {
        $importantPriority  = [];
        $highPriority       = [];
        $normalPriority     = [];

        while($row = mysqli_fetch_array($lines, MYSQLI_ASSOC)) {

            $post = validatePost($row);
            if($post != false && strpos($post['comment'], $term) != false)
            {
                switch($post['priority'])
                {
                    case 3;
                        $normalPriority[] = $post;
                        break;
                    case 2;
                        $highPriority[] = $post;
                        break;
                    case 1;
                        $importantPriority[] = $post;
                        break;
                }
            }


        } // while($row = mysqli_fetch_array($lines, MYSQLI_ASSOC)) {

    } else {
        echo "0 results";
    } // if (mysqli_num_rows($lines) > 0)       


        $posts = array_merge($importantPriority, $highPriority, $normalPriority);

    return $posts;
}

function validatePost($post)
{
    $valid = [];
    $fields = $post;

    if(count($fields) == 8)
    {
        $id  = trim($fields['id']);
        $firstName = trim($fields['firstname']);
        $lastName  = trim($fields['lastname']);
        $title    = trim($fields['title']);
        $comment   = trim($fields['comment']);
        $priority  = trim($fields['priority']);
        $filename  = trim($fields['filename']);
        $time      = trim($fields['time']);

        if($id == '' ||
            $firstName == '' ||
            $lastName == '' ||
            $title    == '' ||
            $comment  == '' ||
            $priority == '' ||
            $filename == '' ||
            $time     == '')
        {
            $valid = false;
        }
        elseif(!file_exists('uploads/'.$filename))
        {
            $valid = false;
        }
        else
        {
            $valid['id'] = $id;
            $valid['firstName'] = $firstName;
            $valid['lastName']  = $lastName;
            $valid['title']     = $title;
            $valid['comment']   = $comment;
            $valid['priority']  = $priority;
            $valid['filename']  = $filename;
            $valid['time']      = $time;
        }
    }

    return $valid;
}

function filterPost($post)
{
    $author     = trim($post['firstName']) . ' ' . trim($post['lastName']);
    $title      = trim($post['title']);
    $comment    = trim($post['comment']);
    $priority   = trim($post['priority']);
    $filename   = trim($post['filename']);
    $postedTime = trim($post['time']);

    $filteredPost['author']     = ucwords(strtolower($author));
    $filteredPost['moment']     = moments(time() - $postedTime);
    $filteredPost['title']      = trim($title);
    $filteredPost['comment']    = trim($comment);
    $filteredPost['priority']   = trim($priority);
    $filteredPost['filename']   = trim($filename);
    $filteredPost['postedTime'] = date('l F \t\h\e dS, Y', $postedTime);
    $filteredPost['searchResultsPostedTime'] = date('M d, \'y', $postedTime);

    return $filteredPost;
}

function validateFields($input)
{
    $valid = [];

    $firstName  = trim($input['firstName']);
    $lastName   = trim($input['lastName']);
    $title      = trim($input['title']);
    $comment    = trim($input['comment']);
    $priority   = trim($input['priority']);

    if($firstName == '' ||
        $lastName == '' ||
        $title    == '' ||
        $comment  == '' ||
        $priority == '' )
    {
        
        $valid = false;
    }
    elseif(!preg_match("/^[A-Z]+$/i", $firstName) || !preg_match("/^[A-Z]+$/i", $lastName) || !preg_match("/^[A-Z]+$/i", $title))
    {
        $valid = false;
    }
    elseif(preg_match("/<|>/", $comment))
    {
        $valid = false;
    }
    elseif(!preg_match("/^[0-9]{1}$/i", $priority))
    {
        $valid = false;
    }
    else
    {
        $valid['firstName'] = $firstName;
        $valid['lastName'] = $lastName;
        $valid['title'] = $title;
        $valid['comment'] = $comment;
        $valid['priority'] = $priority;
    }

    return $valid;
}

function isValidFile($fileInfo)
{
    if($fileInfo['type'] == 'image/jpeg')
    {
        return true;
    }

    return false;
}

function isValidSearchTerm($term)
{
    if(preg_match("/^[A-Z]+$/i", $term))
    {
        return true;
    }

    return false;
}

function insertPost($data,$link)
{
    // md5 is a hashing function http://php.net/manual/en/function.md5.php
    $fileName = md5(time().$data['firstName'].$data['lastName']) . '.jpg';

    move_uploaded_file($data['file'], 'uploads/'.$fileName);

    $line = PHP_EOL;
    $line .= $data['firstName'] . '|';
    $line .= $data['lastName']  . '|';
    $line .= $data['title']     . '|';
    $line .= $data['comment']   . '|';
    $line .= $data['priority']  . '|';
    $line .= $fileName          . '|';
    $line .= time();

$sql = "INSERT INTO posts (firstname,lastname,title,comment,priority,filename,time) VALUES ('".$data['firstName']."','".$data['lastName']."','".$data['title'] ."','".$data['comment']."','".$data['priority']."','".$fileName."','". time() . "');";

$lines = mysqli_query($link, $sql);   

}

function checkSignUp($data)
{
    $valid = false;

    // if any of the fields are missing, return an error
    if(trim($data['firstName']) == '' ||
        trim($data['lastName']) == '' ||
        trim($data['password'])  == '' ||
        trim($data['phoneNumber'])    == '' ||
        trim($data['dob']) == '')
    {
        $valid = "All inputs are required.";
    }
    elseif(!preg_match("/^[A-Z]+$/i", trim($data['firstName'])))
    {
        $valid = 'First Name needs to be alphabetical only.';
    }
    elseif(!preg_match("/^[A-Z]+$/i", trim($data['lastName'])))
    {
        $valid = 'Last Name needs to be alphabetical only';
    }
    elseif(!preg_match("/^.*([0-9]+.*[A-Z])|([A-Z]+.*[0-9]+).*$/i", trim($data['password'])))
    {
        $valid = 'Password must contain at least a number and a letter.';
    }
    elseif(!preg_match("/^((\([0-9]{3}\))|([0-9]{3}))?( |-)?[0-9]{3}( |-)?[0-9]{4}$/", trim($data['phoneNumber'])))
    {
        $valid = 'Phone Number must be in the format of (000) 000 0000.';
    }
    elseif(!preg_match("/^(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)-[0-9]{2}-[0-9]{4}$/i", trim($data['dob'])))
    {
        $valid = 'Date of Birth must be in the format of MMM-DD-YYYY.';
    }
    else
    {
        $valid = true;
    }

    return $valid;
}

// for signup.php
function saveInput($data)
{
    $patterns = array("/-/", "/ /");
    $phoneRegex = preg_replace( $patterns, '', trim($data['phoneNumber']) );

    $line  = $data['firstName'] . ',';
    $line .= $data['lastName']  . ',';
    $line .= $data['password']     . ',';
    $line .= $phoneRegex   . ',';
    $line .= $data['dob']  . ',';
    $line .= time();
    $line .= PHP_EOL;

    // $firstname = $data['firstName'];

    $link = mysqlconnection();
    
    $sql = "INSERT INTO logins (phoneNumber, password, firstname,lastname, dob) VALUES ('".$phoneRegex."', '".$data['password']."','".$data['firstName']."','".$data['lastName']."','".$data['dob']."')";


    $posts = mysqli_query($link, $sql);

    mysqlconnClose($link);
}

// for login.php
function checkInput($input)
{
    $valid = false;

    if(trim($input['phoneNumber']) == '' ||
        trim($input['password']) == '')
    {
        $valid = "All inputs are required.";
    }
    elseif(!preg_match("/^((\([0-9]{3}\))|([0-9]{3}))?( |-)?[0-9]{3}( |-)?[0-9]{4}$/", trim($input['phoneNumber'])))
    {
        $valid = 'Phone Number must be in the format of (000) 000 0000.';

    }
    elseif(!preg_match("/^.*([0-9]+.*[A-Z])|([A-Z]+.*[0-9]+).*$/i", trim($input['password'])))
    {
        $valid = 'Password must contain at least a number and a letter.';
    }
    else
    {
        $valid = true;
    }

    return $valid;
}

// for login.php
function checkLogin($data)
{
    $patterns = array("/-/", "/ /");
    $phoneRegex = preg_replace( $patterns, '', trim($data['phoneNumber']) );

    return $phoneRegex;
}

// for login.php (MySQL later)
function getLogin($phone, $pw)
{
    $posts = [];
    $find = false;

        $link = mysqlconnection();
        $posts = mysqli_query($link, 'SELECT * FROM logins');

        foreach ($posts as $key => $value) {
            
            $words = implode(",", $value);
            $words = preg_split('/,/', $words);
            
            if( $words[1] == $phone )
            {
                if( $words[2] == $pw )
                {
                    $find = true;
                    $_SESSION['login'] = true;
                    $_SESSION['firstName'] = $words[3];
                    $_SESSION['lastName'] = $words[4];
                    break;
                }     

            } // if( $words[3] == $phone )

        } // foreach ($posts as $key => $value) {
    // } // if(file_exists("login.txt"))

        mysqlconnClose($link);

    return $find;
} // function getLogin()



function mysqlconnClose($link)
{
    mysqli_close($link);
}

function mysqlconnection()
{

    $link = mysqli_connect('localhost', 'root', 'root', 'localhost');

    if (!$link) {
        echo mysqli_connect_error();
    }
    else
    {
        return $link;   
    } // if (!$link) {

} // function mysqlconnection()
?>
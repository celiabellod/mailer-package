<?php

class Mail
{ 
    /**
     * @var String
     */
    protected String $recipients = "";

    /**
     * @var String
     */
    protected String $cc = "";

    /**
     * @var String
     */
    protected String $cci = "";

    /**
     * @var String
     */
    protected String $header;

    /**
     * @var String
     */
    protected String $subject = "";

    /**
     * @var String
     */
    protected String $body = "";

    /**
     * @var Array
     */
    protected array $files;

    /**
     * @var String
     */
    protected String $host;

    /**
     * @var Int
     */
    protected Int $port;

    /**
     * @var String
     */
    protected String $smtp;

    /**
     * @var String
     */
    protected String $username;

    /**
     * @var String
     */
    protected String $password;

    /**
     * @var String
     */
    protected String $contentType;

    /**
     * @var Array
     */
    private Array $exception;


    public function __construct(String $host = "localhost", Int $port = 25, String $smtp = "SSL", String $username = "celia@celia.fr", String $password = "blabla", String $contentType = 'Content-type: text/plain;charset=UTF-8')
    {

        $this->header = 'MIME-Version: 1.0' . "\r\n 
                        X-Mailer: PHP/" . phpversion() . "\r\n";

        $this->host = $host;
        $this->port = $port;
        $this->smtp = $smtp;
        $this->username = $username;
        $this->password = $password;
        $this->contentType = $contentType . "\r\n";
    }

    /**
     * @return String Exception message
     * @return Bool
     */
    public function sendMail()
    {
        $this->verifExistRecipient();

        if(!empty($this->exception)) return implode("\n", $this->exception);
    
        $this->addHeaders();
        return (mail($this->recipients, $this->subject, $this->body, $this->header)) ? true : false;
    }

    private function addHeaders(): void
    {
        $this->header .= "To: " . $this->recipients . "\r\n 
                    From:" . $this->username . "\r\n 
                    Subject: $this->subject" . "\r\n";

        $this->header .= $this->contentType;

        if (!empty($this->cc)) {
            $this->header .= "Cc: $this->cc" . "\r\n";
        }

        if (!empty($this->cci)) {
            $this->header .= "Bcc: $this->cci" . "\r\n";
        }
    }

    private function verifExistRecipient() : void {
        try {
            if(empty($this->recipients)){
                throw new Exception('Le destinataire n\'est pas rempli !');
            }
        }catch (Exception $e){
            $this->exception[] = $e->getMessage();
        }
    }

    /**
     * @param Array $file
     * @return String Exception message
     * @return Null
     */
    private function hasFile(array $file) : ?String
    {
        try {
            if ($this->isGoodTypeFile($file)) {
                try {
                    if($file["size"] <= 2097152){
                        $boundary = md5(rand());
                        $handle = fopen($file["tmp_name"], 'r');
                        $content = fread($handle, $file["size"]);
                        $encoded_content = chunk_split(base64_encode($content));
                        fclose($handle);
            
                        $this->body .= "\r\n" . "--" . $boundary . "\r\n
                                Content-type:" . $file['type'] . ";name='" . $file['name'] . "'" . "\n
                                Content-Disposition: attachment; filename='" . $file['name'] . "'" . "\n
                                Content-transfer-encoding:base64" . "\n\n" . $encoded_content . "\n
                                \r\n" . "--" . $boundary . "--" . "\r\n";
            
                        $this->header .= 'multipart/mixed; boundary=' . $boundary . "\r\n";
                        return null;
                    } else {
                        throw new Exception('La taille du fichier '.$file['name'].' n\'est pas valide !');
                    }
                } catch(Exception $e) {
                    $this->exception[] = $e->getMessage();
                    return $e->getMessage();
                }
            } else {
                throw new Exception('Le format du fichier '.$file['name'].' n\'est pas valide !');
            }
        } catch(Exception $e) {
            $this->exception[] = $e->getMessage();
            return $e->getMessage();
        }
    }

    /**
     * @param Array $file
     * @return Bool
     */
    private function isGoodTypeFile(array $file): bool
    {

        $extensionAutorize = [
            "image/jpeg",
            "image/jpg",
            "image/svg",
            "image/bmp",
            "image/png",
            "application/pdf"
        ];

        return (in_array($file['type'], $extensionAutorize)) ? true : false;
    }

    /**
     * Add the value of recipients
     *
     * @param  String  $recipients
     *
     * @return String Exception message || $recipients
     */
    public function addRecipients(String $recipients) : String
    {
        try {
            $this->recipients .= (empty($this->recipients)) ? $recipients :  ', ' . $recipients;

            if ((filter_var($recipients, FILTER_VALIDATE_EMAIL)) === false){
                throw new Exception('Destinataire non valide !');
            } else {
                return $this->recipients;
            }
                
        } catch(Exception $e) {

            $this->exception[] = $e->getMessage();
            return $e->getMessage();
        }
        
    }

    /**
     * Add the value of cc
     *
     * @param  String  $cc
     *
     * @return String Exception message || $cc
     */
    public function addCc(String $cc) : String
    {

        try {
            $this->cc .= (empty($this->cc)) ? $cc :  ', ' . $cc;
            
            if ((filter_var($cc, FILTER_VALIDATE_EMAIL)) === false){
                throw new Exception('Cc non valide !');
            } else {
                return $this->cc;
            }
                
        } catch(Exception $e) {

            $this->exception[] = $e->getMessage();
            return $e->getMessage();
        }
       
    }

    /**
     * Add the value of cci
     *
     * @param  String  $cci
     *
     * @return String Exception message || $cci
     */
    public function addCci(String $cci) : String
    {
        try {
            $this->cci .= (empty($this->cci)) ? $cci :  ', ' . $cci;
            
            if ((filter_var($cci, FILTER_VALIDATE_EMAIL)) === false){
                throw new Exception('Cci non valide !');
            } else {
                return $this->cci;
            }
                
        } catch(Exception $e) {

            $this->exception[] = $e->getMessage();
            return $e->getMessage();
        }
        
    }

    /**
     * Add the value of header
     *
     * @param  String  $header
     *
     * @return  self
     */
    public function addHeader(String $header) : Self
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Add the value of subject
     *
     * @param  String  $subject
     *
     * @return  self
     */
    public function addSubject(String $subject) : Self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Add the value of body
     *
     * @param  String  $body
     *
     * @return  self
     */
    public function addBody(String $body) : Self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Add the value of file
     *
     * @param  Array  $file
     *
     * @return String Exception getMessage
     * @return Array $file
     */
    public function addFile(array $file)
    {
        try {
            if (!empty($file)) {
                $this->files[] = $file;
                $result = $this->hasFile($file);
                return (is_string($result)) ? $result : $file;
                
            } else {
                throw new Exception('Fichiers non valide !');
                $this->header .=  $this->contentType;
            }

        } catch(Exception $e) {
            $this->exception[] = $e->getMessage();
            return $e->getMessage();
        }

    }

    /**
     * Add the value of host
     *
     * @param  String  $host
     *
     * @return  self
     */
    public function addHost(String $host) : Self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Add the value of port
     *
     * @param  Int  $port
     *
     * @return  self
     */
    public function addPort(Int $port) : Self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Add the value of smtp
     *
     * @param  String  $smtp
     *
     * @return  self
     */
    public function addSmtp(String $smtp) : Self
    {
        $this->smtp = $smtp;

        return $this;
    }

    /**
     * Add the value of username
     *
     * @param  String  $username
     *
     * @return  self
     */
    public function addUsername(String $username) : Self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Add the value of password
     *
     * @param  String  $password
     *
     * @return  self
     */
    public function addPassword(String $password) : Self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Add the value of contentType
     *
     * @param  String  $contentType
     *
     * @return  self
     */
    public function addContentType(String $contentType) : Self
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get the value of recipients
     *
     * @return  String
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Set the value of recipients
     *
     * @param  String  $recipients
     *
     * @return  self
     */
    public function setRecipients(String $recipients) : Self
    { 
        $this->recipients = $recipients;

        return $this;
    }

    /**
     * Get the value of cc
     *
     * @return  String
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * Set the value of cc
     *
     * @param  String  $cc
     *
     * @return  self
     */
    public function setCc(String $cc) : Self
    { 
        $this->cc = $cc;

        return $this;
    }

    /**
     * Get the value of cci
     *
     * @return  String
     */
    public function getCci()
    {
        return $this->cci;
    }

    /**
     * Set the value of cci
     *
     * @param  String  $cci
     *
     * @return  self
     */
    public function setCci(String $cci) : Self
    {
        $this->cci = $cci;

        return $this;
    }

    /**
     * Get the value of header
     *
     * @return  String
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set the value of header
     *
     * @param  String  $header
     *
     * @return  self
     */
    public function setHeader(String $header) : Self
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get the value of subject
     *
     * @return  String
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set the value of subject
     *
     * @param  String  $subject
     *
     * @return  self
     */
    public function setSubject(String $subject) : Self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the value of body
     *
     * @return  String
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set the value of body
     *
     * @param  String  $body
     *
     * @return  self
     */
    public function setBody(String $body) : Self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get the value of setFiles
     *
     * @return  Array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Set the value of file
     *
     * @param  Array  $files
     *
     * @return  self
     */
    public function setFiles(array $files) : Self
    {
        $this->files = $files;

        return $this;
    }

    /**
     * Get the value of host
     *
     * @return  String
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Set the value of host
     *
     * @param  String  $host
     *
     * @return  self
     */ 
    public function setHost(String $host): Self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * Get the value of port
     *
     * @return  Integer
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Set the value of port
     *
     * @param  Int $port
     *
     * @return  self
     */
    public function setPort(Int $port) : Self
    {
        $this->port = $port;

        return $this;
    }

    /**
     * Get the value of smtp
     *
     * @return  String
     */
    public function getSmtp()
    {
        return $this->smtp;
    }

    /**
     * Set the value of smtp
     *
     * @param  String  $smtp
     *
     * @return  self
     */
    public function setSmtp(String $smtp) : Self
    {
        $this->smtp = $smtp;

        return $this;
    }

    /**
     * Get the value of username
     *
     * @return  String
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the value of username
     *
     * @param  String  $username
     *
     * @return  self
     */
    public function setUsername(String $username) : Self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get the value of password
     *
     * @return  String
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @param  String  $password
     *
     * @return  self
     */
    public function setPassword(String $password) : Self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of contentType
     *
     * @return  String
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set the value of contentType
     *
     * @param  String  $contentType
     *
     * @return  self
     */
    public function setContentType(String $contentType) : Self
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Add the value of recipients
     *
     * @param  Array  $file
     *
     * @return  self
     */
    public function getIsGoodTypeFile(array $file)
    {
        return $this->isGoodTypeFile($file);
    }
}

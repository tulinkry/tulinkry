<?php

namespace Tulinkry\Model\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\MappedSuperclass()
 */
abstract class User extends BaseEntity
{

	const STATE_OK = 0;
	const STATE_NEEDLOGOUT = 1;
	const STATE_BANNED = 2;
	const STATE_BLOCKED = 4;
	const STATE_USERNAME_CHANGED = 8;

    const USERNAME_PATTERN = "^([a-zA-Z0-9_\-ěščřžýáíéóúůďťňĎŇŤŠČŘŽÝÁÍÉÚŮ]{1,})([a-zA-Z0-9ěščřžýáíéóúůďťňĎŇŤŠČŘŽÝÁÍÉÚŮ]{1,}[a-zA-Z0-9_\-ěščřžýáíéóúůďťňĎŇŤŠČŘŽÝÁÍÉÚŮ]{1,})$"; //[a-zA-Z0-9ěščřžýáíéóúůďťňĎŇŤŠČŘŽÝÁÍÉÚŮ\\$\*\+\{\}\(\)\-\_\!\?\:\~\^\#\@\™]{3,}";

	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="user_id", type="integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	private $id;

	/**
	 * @ORM\Column(unique=true)
	 * @var string
	 */
	private $username;
	/**
	 * @ORM\Column(unique=true)
	 * @var string
	 */
	private $email;
	/**
	 * @ORM\Column
	 * @var string
	 */
	private $password;
	/**
	 * @ORM\Column ( type="datetime" )
	 */
	protected $click = "0000-00-00 00:00:00";

	/**
	 * @ORM\Column ( type="integer" )
	 */
	protected $skin = 1;
	/**
	 * @ORM\Column ( type="string", length=15 )
	 */
	protected $ip = "000.000.000.000";

	/**
	 * @ORM\Column ( type="datetime" )
	 */
	protected $registration;
	
	/**
	 * @ORM\Column ( type="integer" )
	 */
	protected $state = self::STATE_OK;


	public function __construct ()
	{
		$this -> click = new \Tulinkry\DateTime ( date ( "Y-m-d H:i:s" ) );
        $this -> registration = new \Tulinkry\DateTime ( "1970-01-01 00:00:00" );
	}

    public function getDescription ()
    {
    	$d = $this -> getId ();
    	if ( self::useId )
    		$d .= " [" . $this -> getId () . "]";
    	return $d;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set click
     *
     * @param \DateTime $click
     *
     * @return User
     */
    public function setClick($click)
    {
        $this->click = $click;

        return $this;
    }

    /**
     * Get click
     *
     * @return \DateTime
     */
    public function getClick()
    {
        return $this->click;
    }

    /**
     * Set skin
     *
     * @param integer $skin
     *
     * @return User
     */
    public function setSkin($skin)
    {
        $this->skin = $skin;

        return $this;
    }

    /**
     * Get skin
     *
     * @return integer
     */
    public function getSkin()
    {
        return $this->skin;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return User
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set registration
     *
     * @param \DateTime $registration
     *
     * @return User
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;

        return $this;
    }

    /**
     * Get registration
     *
     * @return \DateTime
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * Set state
     *
     * @param integer $state
     *
     * @return User
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set state
     *
     * @return User
     */
    public function unsetNeedLogout()
    {
        $this->state = $this -> state & ~self::STATE_NEEDLOGOUT;

        return $this;
    }

    /**
     * Set state
     *
     * @return User
     */
    public function setNeedLogout()
    {
        $this->state = $this -> state | self::STATE_NEEDLOGOUT;

        return $this;
    }

    /**
     * Is state
     *
     * @return boolean
     */
    public function needLogout()
    {
        return $this->state & self::STATE_NEEDLOGOUT;
    }

    /**
     * Set state
     *
     * @return User
     */
    public function unsetUsernameChanged()
    {
        $this->state = $this -> state & ~self::STATE_USERNAME_CHANGED;

        return $this;
    }

    /**
     * Set state
     *
     * @return User
     */
    public function setUsernameChanged()
    {
        $this->state = $this -> state | self::STATE_USERNAME_CHANGED;

        return $this;
    }

    /**
     * Is state
     *
     * @return boolean
     */
    public function usernameChanged()
    {
        return $this->state & self::STATE_USERNAME_CHANGED;
    }
    
}

<?php
/**
 *  Base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage WebTester
 *  @version    $Id: Cookies.php 1784 2008-04-26 13:07:14Z pp11 $
 */

/**#@+
 *  include other SimpleTest class files
 */
require_once(dirname(__FILE__) . '/url.php');
/**#@-*/

/**
 *    Cookie data holder. Cookie rules are full of pretty
 *    arbitary stuff. I have used...
 *    http://wp.netscape.com/newsref/std/Cookie_spec.html
 *    http://www.Cookiecentral.com/faq/
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleCookie {
    private $host;
    private $name;
    private $value;
    private $path;
    private $expiry;
    private $is_secure;
    
    /**
     *    Constructor. Sets the stored values.
     *    @param string $name            Cookie key.
     *    @param string $value           Value of Cookie.
     *    @param string $path            Cookie path if not host wide.
     *    @param string $expiry          Expiry date as string.
     *    @param boolean $is_secure      Currently ignored.
     */
    function __construct($name, $value = false, $path = false, $expiry = false, $is_secure = false) {
        $this->host = false;
        $this->name = $name;
        $this->value = $value;
        $this->path = ($path ? $this->fixPath($path) : "/");
        $this->expiry = false;
        if (is_string($expiry)) {
            $this->expiry = strtotime($expiry);
        } elseif (is_integer($expiry)) {
            $this->expiry = $expiry;
        }
        $this->is_secure = $is_secure;
    }
    
    /**
     *    Sets the host. The Cookie rules determine
     *    that the first two parts are taken for
     *    certain TLDs and three for others. If the
     *    new host does not match these rules then the
     *    call will fail.
     *    @param string $host       New hostname.
     *    @return boolean           True if hostname is valid.
     *    @access public
     */
    function setHost($host) {
        if ($host = $this->truncateHost($host)) {
            $this->host = $host;
            return true;
        }
        return false;
    }
    
    /**
     *    Accessor for the truncated host to which this
     *    Cookie applies.
     *    @return string       Truncated hostname.
     *    @access public
     */
    function getHost() {
        return $this->host;
    }
    
    /**
     *    Test for a Cookie being valid for a host name.
     *    @param string $host    Host to test against.
     *    @return boolean        True if the Cookie would be valid
     *                           here.
     */
    function isValidHost($host) {
        return ($this->truncateHost($host) === $this->getHost());
    }
    
    /**
     *    Extracts just the domain part that determines a
     *    Cookie's host validity.
     *    @param string $host    Host name to truncate.
     *    @return string        Domain or false on a bad host.
     *    @access private
     */
    protected function truncateHost($host) {
        $tlds = SimpleUrl::getAllTopLevelDomains();
        if (preg_match('/[a-z\-]+\.(' . $tlds . ')$/i', $host, $matches)) {
            return $matches[0];
        } elseif (preg_match('/[a-z\-]+\.[a-z\-]+\.[a-z\-]+$/i', $host, $matches)) {
            return $matches[0];
        }
        return false;
    }
    
    /**
     *    Accessor for name.
     *    @return string       Cookie key.
     *    @access public
     */
    function getName() {
        return $this->name;
    }
    
    /**
     *    Accessor for value. A deleted Cookie will
     *    have an empty string for this.
     *    @return string       Cookie value.
     *    @access public
     */
    function getValue() {
        return $this->value;
    }
    
    /**
     *    Accessor for path.
     *    @return string       Valid Cookie path.
     *    @access public
     */
    function getPath() {
        return $this->path;
    }
    
    /**
     *    Tests a path to see if the Cookie applies
     *    there. The test path must be longer or
     *    equal to the Cookie path.
     *    @param string $path       Path to test against.
     *    @return boolean           True if Cookie valid here.
     *    @access public
     */
    function isValidPath($path) {
        return (strncmp(
                $this->fixPath($path),
                $this->getPath(),
                strlen($this->getPath())) == 0);
    }
    
    /**
     *    Accessor for expiry.
     *    @return string       Expiry string.
     *    @access public
     */
    function getExpiry() {
        if (! $this->expiry) {
            return false;
        }
        return gmdate("D, d M Y H:i:s", $this->expiry) . " GMT";
    }
    
    /**
     *    Test to see if Cookie is expired against
     *    the Cookie format time or timestamp.
     *    Will give true for a session Cookie.
     *    @param integer/string $now  Time to test against. Result
     *                                will be false if this time
     *                                is later than the Cookie expiry.
     *                                Can be either a timestamp integer
     *                                or a Cookie format date.
     *    @access public
     */
    function isExpired($now) {
        if (! $this->expiry) {
            return true;
        }
        if (is_string($now)) {
            $now = strtotime($now);
        }
        return ($this->expiry < $now);
    }
    
    /**
     *    Ages the Cookie by the specified number of
     *    seconds.
     *    @param integer $interval   In seconds.
     *    @public
     */
    function agePrematurely($interval) {
        if ($this->expiry) {
            $this->expiry -= $interval;
        }
    }
    
    /**
     *    Accessor for the secure flag.
     *    @return boolean       True if Cookie needs SSL.
     *    @access public
     */
    function isSecure() {
        return $this->is_secure;
    }
    
    /**
     *    Adds a trailing and leading slash to the path
     *    if missing.
     *    @param string $path            Path to fix.
     *    @access private
     */
    protected function fixPath($path) {
        if (substr($path, 0, 1) != '/') {
            $path = '/' . $path;
        }
        if (substr($path, -1, 1) != '/') {
            $path .= '/';
        }
        return $path;
    }
}

/**
 *    Repository for Cookies. This stuff is a
 *    tiny bit browser dependent.
 *    @package SimpleTest
 *    @subpackage WebTester
 */
class SimpleCookieJar {
    private $Cookies;
    
    /**
     *    Constructor. Jar starts empty.
     *    @access public
     */
    function __construct() {
        $this->Cookies = array();
    }
    
    /**
     *    Removes expired and temporary Cookies as if
     *    the browser was closed and re-opened.
     *    @param string/integer $now   Time to test expiry against.
     *    @access public
     */
    function restartSession($date = false) {
        $surviving_Cookies = array();
        for ($i = 0; $i < count($this->Cookies); $i++) {
            if (! $this->Cookies[$i]->getValue()) {
                continue;
            }
            if (! $this->Cookies[$i]->getExpiry()) {
                continue;
            }
            if ($date && $this->Cookies[$i]->isExpired($date)) {
                continue;
            }
            $surviving_Cookies[] = $this->Cookies[$i];
        }
        $this->Cookies = $surviving_Cookies;
    }
    
    /**
     *    Ages all Cookies in the Cookie jar.
     *    @param integer $interval     The old session is moved
     *                                 into the past by this number
     *                                 of seconds. Cookies now over
     *                                 age will be removed.
     *    @access public
     */
    function agePrematurely($interval) {
        for ($i = 0; $i < count($this->Cookies); $i++) {
            $this->Cookies[$i]->agePrematurely($interval);
        }
    }
    
    /**
     *    Sets an additional Cookie. If a Cookie has
     *    the same name and path it is replaced.
     *    @param string $name       Cookie key.
     *    @param string $value      Value of Cookie.
     *    @param string $host       Host upon which the Cookie is valid.
     *    @param string $path       Cookie path if not host wide.
     *    @param string $expiry     Expiry date.
     *    @access public
     */
    function setCookie($name, $value, $host = false, $path = '/', $expiry = false) {
        $Cookie = new SimpleCookie($name, $value, $path, $expiry);
        if ($host) {
            $Cookie->setHost($host);
        }
        $this->Cookies[$this->findFirstMatch($Cookie)] = $Cookie;
    }
    
    /**
     *    Finds a matching Cookie to write over or the
     *    first empty slot if none.
     *    @param SimpleCookie $Cookie    Cookie to write into jar.
     *    @return integer                Available slot.
     *    @access private
     */
    protected function findFirstMatch($Cookie) {
        for ($i = 0; $i < count($this->Cookies); $i++) {
            $is_match = $this->isMatch(
                    $Cookie,
                    $this->Cookies[$i]->getHost(),
                    $this->Cookies[$i]->getPath(),
                    $this->Cookies[$i]->getName());
            if ($is_match) {
                return $i;
            }
        }
        return count($this->Cookies);
    }
    
    /**
     *    Reads the most specific Cookie value from the
     *    browser Cookies. Looks for the longest path that
     *    matches.
     *    @param string $host        Host to search.
     *    @param string $path        Applicable path.
     *    @param string $name        Name of Cookie to read.
     *    @return string             False if not present, else the
     *                               value as a string.
     *    @access public
     */
    function getCookieValue($host, $path, $name) {
        $longest_path = '';
        foreach ($this->Cookies as $Cookie) {
            if ($this->isMatch($Cookie, $host, $path, $name)) {
                if (strlen($Cookie->getPath()) > strlen($longest_path)) {
                    $value = $Cookie->getValue();
                    $longest_path = $Cookie->getPath();
                }
            }
        }
        return (isset($value) ? $value : false);
    }
    
    /**
     *    Tests Cookie for matching against search
     *    criteria.
     *    @param SimpleTest $Cookie    Cookie to test.
     *    @param string $host          Host must match.
     *    @param string $path          Cookie path must be shorter than
     *                                 this path.
     *    @param string $name          Name must match.
     *    @return boolean              True if matched.
     *    @access private
     */
    protected function isMatch($Cookie, $host, $path, $name) {
        if ($Cookie->getName() != $name) {
            return false;
        }
        if ($host && $Cookie->getHost() && ! $Cookie->isValidHost($host)) {
            return false;
        }
        if (! $Cookie->isValidPath($path)) {
            return false;
        }
        return true;
    }
    
    /**
     *    Uses a URL to sift relevant Cookies by host and
     *    path. Results are list of strings of form "name=value".
     *    @param SimpleUrl $url       Url to select by.
     *    @return array               Valid name and value pairs.
     *    @access public
     */
    function selectAsPairs($url) {
        $pairs = array();
        foreach ($this->Cookies as $Cookie) {
            if ($this->isMatch($Cookie, $url->getHost(), $url->getPath(), $Cookie->getName())) {
                $pairs[] = $Cookie->getName() . '=' . $Cookie->getValue();
            }
        }
        return $pairs;
    }
}
?>
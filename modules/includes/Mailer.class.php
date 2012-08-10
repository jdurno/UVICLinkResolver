<?php

/*
	mailer.class.php
	send email messages from the application
	mostly just a wrapper for PHP's mail function
*/
/*
    copyright 2011,2012 University of Victoria Libraries

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/


class Mailer
{
	var $fromName;
	var $fromAddress;
	var $toAddress;
	var $subjectLine;
	var $content;
	var $headers;
	var $html;
	
	function Mailer()
	{
		$this->fromName = NULL;
		$this->fromAddress = NULL;
		$this->toAddress = NULL;
		$this->subjectLine = NULL;
		$this->content = NULL;
		$this->html = FALSE;
	}
	
	function setFromName($var)
	{
		$this->fromName = $var;
	}

	function setFromAddress($var)
	{
		$this->fromAddress = $var;
	}
	
	function setToAddress($var)
	{
		$this->toAddress = $var;
	}
	
	function setSubjectLine($var)
	{
		$this->subjectLine = $var;
	}
	
	function setContent($var)
	{
		$this->content = $var . "\n";
	}
	
	function appendToContent($var)
	{
		$this->content .= "$var\n";
	}
	
	function setHTML($var) 
	{
		$this->html = $var;	
	}
	
	function sendEmail()
	{
	
		if (!$this->content)
		{
			return FALSE;
		}
		
		if ($this->fromName)
		{
			$fromName = $this->fromName;
		}
		else
		{
			$fromName = 'UVic Libraries Web Site';
		}
		
		if ($this->fromAddress)
		{	
			$fromAddress = $this->fromAddress;
		}
		else
		{
			$fromAddress = 'Do_not_reply@uvic.ca';
		}
		
		if ($this->toAddress)
		{
			$toAddress = $this->toAddress;
		}
		
		if ($this->subjectLine)
		{
			$subjectLine = $this->subjectLine;
		}
		else
		{
			$subjectLine = "Message from Libraries Website";
		}		
		
		$this->headers = 	"From: $fromName <$fromAddress>\n";
		$this->headers .= 	"X-Sender: <$fromAddress>\n";
		$this->headers .=	"X-Mailer: PHP\n";
		$this->headers .=	"X-Priority: Normal\n";
		$this->headers .=	"Return-Path: <$fromAddress>\n";
		$this->headers .= 	"MIME-Version: 1.0\r\n";
		
		if ($this->html) {
			$this->headers .= "Content-Type: text/html; charset=UTF-8\r\n";
		}
		
		$success = mail($toAddress, $subjectLine, $this->content, $this->headers);
		
		if ($success)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	
	}
	
}






?>
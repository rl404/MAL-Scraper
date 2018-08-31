<?php

namespace MalScraper;

require "scraper\mal_scraper.php";

class MalScraper {

	/**
	 * Get anime/manga information
	 *
	 * @param 	string 	$type 	anime or manga
	 * @param 	integer	$id 	id of the anime or manga
	 * @return 	json 	\scraper\getInfo
	 */
	public static function getInfo() {
		header('Content-Type: application/json');
		return call_user_func_array ( '\scraper\getInfo' , func_get_args() );
	}

	/**
	 * Get character information
	 *
	 * @param 	integer	$id 	id of character
	 * @return 	json 	\scraper\getCharacter
	 */
	public static function getCharacter() {
		header('Content-Type: application/json');
		return call_user_func_array ( '\scraper\getCharacter' , func_get_args() );
	}

	/**
	 * Get complete list of character and staff of anime or manga
	 *
	 * @param 	string	$type 	anime or manga
	 * @param 	integer	$id 	id of the anime or manga
	 * @return 	json 	\scraper\getCharacterStaff
	 */
	public static function getCharacterStaff() {
		header('Content-Type: application/json');
		return call_user_func_array ( '\scraper\getCharacterStaff' , func_get_args() );
	}

	/**
	 * Get people information
	 *
	 * @param 	integer	$id 	id of people
	 * @return 	json 	\scraper\getPeople
	 */
	public static function getPeople() {
		header('Content-Type: application/json');
		return call_user_func_array ( '\scraper\getPeople' , func_get_args() );
	}

	/**
	 * Get list of result of anime search
	 *
	 * @param 	string	$q 		search query
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\searchAnime
	 */
	public static function searchAnime() {
		header('Content-Type: application/json');
		return call_user_func_array ( '\scraper\searchAnime' , func_get_args() );
	}

	/**
	 * Get list of result of manga search
	 *
	 * @param 	string	$q 		search query
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\searchManga
	 */
	public static function searchManga() {
		header('Content-Type: application/json');
		return call_user_func_array ( '\scraper\searchManga' , func_get_args() );
	}

	/**
	 * Get list of result of character search
	 *
	 * @param 	string	$q 		search query
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\searchCharacter
	 */
	public static function searchCharacter() {
		header('Content-Type: application/json');
		return call_user_func_array ( '\scraper\searchCharacter' , func_get_args() );
	}

	/**
	 * Get list of result of people search
	 *
	 * @param 	string	$q 		search query
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\searchPeople
	 */
	public static function searchPeople() {
		header('Content-Type: application/json');
		return call_user_func_array ( '\scraper\searchPeople' , func_get_args() );
	}

	/**
	 * Get list of anime of the season
	 *
	 * @param 	string	$year 		year of the season (current year for default)
	 * @param 	string	$season 	summer, spring, fall, winter (current season for default)
	 * @return 	json 	\scraper\getSeason
	 */
	public static function getSeason() {
		header('Content-Type: application/json');
		return call_user_func_array ( '\scraper\getSeason' , func_get_args() );
	}

	/**
	 * Get list of top anime
	 *
	 * @param 	integer	$type 	type of top anime
	 * @param 	integer	$page 	page of top list
	 * @return 	json 	\scraper\getTopAnime
	 */
	public static function getTopAnime() {
		header('Content-Type: application/json');
		return call_user_func_array ( '\scraper\getTopAnime' , func_get_args() );
	}

	/**
	 * Get list of top manga
	 *
	 * @param 	integer	$type 	type of top manga
	 * @param 	integer	$page 	page of top list
	 * @return 	json 	\scraper\getTopManga
	 */
	public static function getTopManga() {
		header('Content-Type: application/json');
		return call_user_func_array ( '\scraper\getTopManga' , func_get_args() );
	}

	// WIP
	public static function getCover() {
		header("Content-type: text/css; charset: UTF-8");
		return call_user_func_array ( '\scraper\getCover' , func_get_args() );
	}
}
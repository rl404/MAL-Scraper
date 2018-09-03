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
		return call_user_func_array ( '\scraper\getInfo' , func_get_args() );
	}

	/**
	 * Get character information
	 *
	 * @param 	integer	$id 	id of character
	 * @return 	json 	\scraper\getCharacter
	 */
	public static function getCharacter() {
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
		return call_user_func_array ( '\scraper\getCharacterStaff' , func_get_args() );
	}

	/**
	 * Get people information
	 *
	 * @param 	integer	$id 	id of people
	 * @return 	json 	\scraper\getPeople
	 */
	public static function getPeople() {
		return call_user_func_array ( '\scraper\getPeople' , func_get_args() );
	}

	/**
	 * Get list of anime produced by selected studio/producer
	 *
	 * @param 	integer	$id 	id of studio/producer
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\getStudioProducer
	 */
	public static function getStudioProducer() {
		return call_user_func_array ( '\scraper\getStudioProducer' , func_get_args() );
	}

	/**
	 * Get list of manga produced by selected magazine
	 *
	 * @param 	integer	$id 	id of magazine
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\getMagazine
	 */
	public static function getMagazine() {
		return call_user_func_array ( '\scraper\getMagazine' , func_get_args() );
	}

	/**
	 * Get list of anime contain selected genre
	 *
	 * @param 	string	$type 	anime or manga
	 * @param 	integer	$id 	id of genre
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\getGenre
	 */
	public static function getGenre() {
		return call_user_func_array ( '\scraper\getGenre' , func_get_args() );
	}

	/**
	 * Get list of all anime genre
	 *
	 * @return 	json 	\scraper\getAllAnimeGenre
	 */
	public static function getAllAnimeGenre() {
		return call_user_func_array ( '\scraper\getAllAnimeGenre' , func_get_args() );
	}

	/**
	 * Get list of all manga genre
	 *
	 * @return 	json 	\scraper\getAllMangaGenre
	 */
	public static function getAllMangaGenre() {
		return call_user_func_array ( '\scraper\getAllMangaGenre' , func_get_args() );
	}

	/**
	 * Get list of all anime studio/producer
	 *
	 * @return 	json 	\scraper\getAllStudioProducer
	 */
	public static function getAllStudioProducer() {
		return call_user_func_array ( '\scraper\getAllStudioProducer' , func_get_args() );
	}

	/**
	 * Get list of all manga magazine
	 *
	 * @return 	json 	\scraper\getAllMagazine
	 */
	public static function getAllMagazine() {
		return call_user_func_array ( '\scraper\getAllMagazine' , func_get_args() );
	}

	/**
	 * Get list of result of anime search
	 *
	 * @param 	string	$q 		search query
	 * @param 	integer	$page 	page of result list
	 * @return 	json 	\scraper\searchAnime
	 */
	public static function searchAnime() {
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
		return call_user_func_array ( '\scraper\getTopManga' , func_get_args() );
	}

	// WIP
	public static function getCover() {
		header("Content-type: text/css; charset: UTF-8");
		return call_user_func_array ( '\scraper\getCover' , func_get_args() );
	}
}
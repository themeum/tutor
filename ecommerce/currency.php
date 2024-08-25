<?php
/**
 * Currency List
 *
 * @package Tutor\Includes
 * @author Themeum <support@themeum.com>
 * @link https=>//themeum.com
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'tutor_currencies' ) ) {
	/**
	 * Get tutor currencies
	 *
	 * @since 3.0.0
	 *
	 * @return array
	 */
	function get_tutor_currencies() {
		$currencies = array(
			array(
				'code'         => 'USD',
				'symbol'       => '$',
				'name'         => 'US Dollar',
				'locale'       => 'en-us',
				'numeric_code' => 840,
			),
			array(
				'code'         => 'EUR',
				'symbol'       => "€",
				'name'         => 'Euro',
				'locale'       => 'en-eu',
				'numeric_code' => 978,
			),
			array(
				'code'         => 'GBP',
				'symbol'       => "£",
				'name'         => 'British Pound',
				'locale'       => 'en-gb',
				'numeric_code' => 826,
			),
			array(
				'code'         => 'CAD',
				'symbol'       => '$',
				'name'         => 'Canadian Dollar',
				'locale'       => 'en-ca',
				'numeric_code' => 124,
			),
			array(
				'code'         => 'AED',
				'symbol'       => 'AED',
				'name'         => 'UAE Dirham',
				'locale'       => 'ar-ae',
				'numeric_code' => 784,
			),
			array(
				'code'         => 'AFN',
				'symbol'       => "؋",
				'name'         => 'Afghanistan Afghani',
				'locale'       => 'fa-af',
				'numeric_code' => 971,
			),
			array(
				'code'         => 'ALL',
				'symbol'       => 'Lek',
				'name'         => "Albanian Lek\u00eb",
				'locale'       => 'sq-al',
				'numeric_code' => 8,
			),
			array(
				'code'         => 'AMD',
				'symbol'       => 'AMD',
				'name'         => 'Armenian Dram',
				'locale'       => 'hy-am',
				'numeric_code' => 51,
			),
			array(
				'code'         => 'ANG',
				'symbol'       => "ƒ",
				'name'         => 'Netherlands Antillean Gulden',
				'locale'       => 'nl-an',
				'numeric_code' => 532,
			),
			array(
				'code'         => 'AOA',
				'symbol'       => 'Kz',
				'name'         => 'Angolan Kwanza',
				'locale'       => 'pt-ao',
				'numeric_code' => 973,
			),
			array(
				'code'         => 'ARS',
				'symbol'       => '$',
				'name'         => 'Argentine Peso',
				'locale'       => 'es-ar',
				'numeric_code' => 32,
			),
			array(
				'code'         => 'AUD',
				'symbol'       => '$',
				'name'         => 'Australian Dollar',
				'locale'       => 'en-au',
				'numeric_code' => 36,
			),
			array(
				'code'         => 'AWG',
				'symbol'       => 'AWG',
				'name'         => 'Aruban Florin',
				'locale'       => 'nl-aw',
				'numeric_code' => 533,
			),
			array(
				'code'         => 'AZN',
				'symbol'       => "₼",
				'name'         => 'Azerbaijani Manat',
				'locale'       => 'az-az',
				'numeric_code' => 944,
			),
			array(
				'code'         => 'BAM',
				'symbol'       => 'KM',
				'name'         => 'Bosnia and Herzegovina Convertible Mark',
				'locale'       => 'bs-ba',
				'numeric_code' => 977,
			),
			array(
				'code'         => 'BBD',
				'symbol'       => '$',
				'name'         => 'Barbadian Dollar',
				'locale'       => 'en-bb',
				'numeric_code' => 52,
			),
			array(
				'code'         => 'BDT',
				'symbol'       => "৳",
				'name'         => 'Bangladeshi Taka',
				'locale'       => 'bn-bd',
				'numeric_code' => 50,
			),
			array(
				'code'         => 'BGN',
				'symbol'       => 'BGN',
				'name'         => 'Bulgarian Leva',
				'locale'       => 'bg-bg',
				'numeric_code' => 975,
			),
			array(
				'code'         => 'BHD',
				'symbol'       => 'BHD',
				'name'         => 'Bahraini Dinar',
				'locale'       => 'ar-bh',
				'numeric_code' => 48,
			),
			array(
				'code'         => 'BIF',
				'symbol'       => 'FBu',
				'name'         => 'Burundi Franc',
				'locale'       => 'fr-bi',
				'numeric_code' => 108,
			),
			array(
				'code'         => 'BMD',
				'symbol'       => 'BD$',
				'name'         => 'Bermudian Dollar',
				'locale'       => 'en-bm',
				'numeric_code' => 60,
			),
			array(
				'code'         => 'BND',
				'symbol'       => 'B$',
				'name'         => 'Brunei Dollar',
				'locale'       => 'ms-bn',
				'numeric_code' => 96,
			),
			array(
				'code'         => 'BOB',
				'symbol'       => 'Bs',
				'name'         => 'Bolivian Boliviano',
				'locale'       => 'es-bo',
				'numeric_code' => 68,
			),
			array(
				'code'         => 'BRL',
				'symbol'       => 'R$',
				'name'         => 'Brazilian Real',
				'locale'       => 'pt-br',
				'numeric_code' => 986,
			),
			array(
				'code'         => 'BSD',
				'symbol'       => 'B$',
				'name'         => 'Bahamian Dollar',
				'locale'       => 'en-bs',
				'numeric_code' => 44,
			),
			array(
				'code'         => 'BTN',
				'symbol'       => 'Nu.',
				'name'         => 'Bhutanese Ngultrum',
				'locale'       => 'dz-bt',
				'numeric_code' => 64,
			),
			array(
				'code'         => 'BWP',
				'symbol'       => 'P',
				'name'         => 'Botswana Pula',
				'locale'       => 'en-bw',
				'numeric_code' => 72,
			),
			array(
				'code'         => 'BYN',
				'symbol'       => 'BYN',
				'name'         => 'Belarusian Ruble',
				'locale'       => 'be-by',
				'numeric_code' => 933,
			),
			array(
				'code'         => 'BZD',
				'symbol'       => '$',
				'name'         => 'Belize Dollar',
				'locale'       => 'en-bz',
				'numeric_code' => 84,
			),
			array(
				'code'         => 'CDF',
				'symbol'       => 'FDC',
				'name'         => 'Congolese Franc',
				'locale'       => 'fr-cd',
				'numeric_code' => 976,
			),
			array(
				'code'         => 'CHF',
				'symbol'       => 'CHF',
				'name'         => 'Swiss Franc',
				'locale'       => 'fr-ch',
				'numeric_code' => 756,
			),
			array(
				'code'         => 'CLP',
				'symbol'       => '$',
				'name'         => 'Chilean Peso',
				'locale'       => 'es-cl',
				'numeric_code' => 152,
			),
			array(
				'code'         => 'CNY',
				'symbol'       => "¥",
				'name'         => 'Chinese\/Yuan Renminbi',
				'locale'       => 'zh-cn',
				'numeric_code' => 156,
			),
			array(
				'code'         => 'COP',
				'symbol'       => '$',
				'name'         => 'Colombian Peso',
				'locale'       => 'es-co',
				'numeric_code' => 170,
			),
			array(
				'code'         => 'CRC',
				'symbol'       => "₡",
				'name'         => 'Costa Rican Colon',
				'locale'       => 'es-cr',
				'numeric_code' => 188,
			),
			array(
				'code'         => 'CUP',
				'symbol'       => '$',
				'name'         => 'Cuban Peso',
				'locale'       => 'es-cu',
				'numeric_code' => 192,
			),
			array(
				'code'         => 'CVE',
				'symbol'       => 'CVE',
				'name'         => 'Cape Verdean Escudo',
				'locale'       => 'pt-cv',
				'numeric_code' => 132,
			),
			array(
				'code'         => 'CZK',
				'symbol'       => "Kč",
				'name'         => 'Czech Koruna',
				'locale'       => 'cs-cz',
				'numeric_code' => 203,
			),
			array(
				'code'         => 'DJF',
				'symbol'       => 'Fdj',
				'name'         => 'Djiboutian Franc',
				'locale'       => 'fr-dj',
				'numeric_code' => 262,
			),
			array(
				'code'         => 'DKK',
				'symbol'       => 'Kr.',
				'name'         => 'Danish Krone',
				'locale'       => 'da-dk',
				'numeric_code' => 208,
			),
			array(
				'code'         => 'DOP',
				'symbol'       => '$',
				'name'         => 'Dominican Peso',
				'locale'       => 'es-do',
				'numeric_code' => 214,
			),
			array(
				'code'         => 'DZD',
				'symbol'       => 'DZD',
				'name'         => 'Algerian Dinar',
				'locale'       => 'ar-dz',
				'numeric_code' => 12,
			),
			array(
				'code'         => 'EEK',
				'symbol'       => 'KR',
				'name'         => 'Estonian Kroon',
				'locale'       => 'et-ee',
				'numeric_code' => 233,
			),
			array(
				'code'         => 'EGP',
				'symbol'       => "£",
				'name'         => 'Egyptian Pound',
				'locale'       => 'ar-eg',
				'numeric_code' => 818,
			),
			array(
				'code'         => 'ERN',
				'symbol'       => 'ERN',
				'name'         => 'Eritrean Nakfa',
				'locale'       => 'ti-er',
				'numeric_code' => 232,
			),
			array(
				'code'         => 'ETB',
				'symbol'       => 'ETB',
				'name'         => 'Ethiopian Birr',
				'locale'       => 'am-et',
				'numeric_code' => 230,
			),
			array(
				'code'         => 'FJD',
				'symbol'       => '$',
				'name'         => 'Fijian Dollar',
				'locale'       => 'en-fj',
				'numeric_code' => 242,
			),
			array(
				'code'         => 'FKP',
				'symbol'       => "£",
				'name'         => 'Falkland Islands Pound',
				'locale'       => 'en-fk',
				'numeric_code' => 238,
			),
			array(
				'code'         => 'GEL',
				'symbol'       => 'GEL',
				'name'         => 'Georgian Lari',
				'locale'       => 'ka-ge',
				'numeric_code' => 981,
			),
			array(
				'code'         => 'GHS',
				'symbol'       => "₵",
				'name'         => 'Ghanaian Cedi',
				'locale'       => 'en-gh',
				'numeric_code' => 936,
			),
			array(
				'code'         => 'GIP',
				'symbol'       => "£",
				'name'         => 'Gibraltar Pound',
				'locale'       => 'en-gi',
				'numeric_code' => 292,
			),
			array(
				'code'         => 'GMD',
				'symbol'       => 'D',
				'name'         => 'Gambian Dalasi',
				'locale'       => 'en-gm',
				'numeric_code' => 270,
			),
			array(
				'code'         => 'GNF',
				'symbol'       => 'FG',
				'name'         => 'Guinean franc',
				'locale'       => 'fr-gn',
				'numeric_code' => 324,
			),
			array(
				'code'         => 'XAF',
				'symbol'       => 'F.CFA',
				'name'         => 'Central African CFA Franc',
				'locale'       => 'fr-xa',
				'numeric_code' => 950,
			),
			array(
				'code'         => 'GTQ',
				'symbol'       => 'Q',
				'name'         => 'Guatemalan Quetzal',
				'locale'       => 'es-gt',
				'numeric_code' => 320,
			),
			array(
				'code'         => 'GYD',
				'symbol'       => '$',
				'name'         => 'Guyanese Dollar',
				'locale'       => 'en-gy',
				'numeric_code' => 328,
			),
			array(
				'code'         => 'HKD',
				'symbol'       => '$',
				'name'         => 'Hong Kong Dollar',
				'locale'       => 'zh-hk',
				'numeric_code' => 344,
			),
			array(
				'code'         => 'HNL',
				'symbol'       => 'L',
				'name'         => 'Honduran Lempira',
				'locale'       => 'es-hn',
				'numeric_code' => 340,
			),
			array(
				'code'         => 'HRK',
				'symbol'       => 'kn',
				'name'         => 'Croatian Kuna',
				'locale'       => 'hr-hr',
				'numeric_code' => 191,
			),
			array(
				'code'         => 'HTG',
				'symbol'       => 'G',
				'name'         => 'Haitian Gourde',
				'locale'       => 'ht-ht',
				'numeric_code' => 332,
			),
			array(
				'code'         => 'HUF',
				'symbol'       => 'Ft',
				'name'         => 'Hungarian Forint',
				'locale'       => 'hu-hu',
				'numeric_code' => 348,
			),
			array(
				'code'         => 'IDR',
				'symbol'       => 'Rp',
				'name'         => 'Indonesian Rupiah',
				'locale'       => 'id-id',
				'numeric_code' => 360,
			),
			array(
				'code'         => 'ILS',
				'symbol'       => "₪",
				'name'         => 'Israeli New Sheqel',
				'locale'       => 'he-il',
				'numeric_code' => 376,
			),
			array(
				'code'         => 'INR',
				'symbol'       => "₹",
				'name'         => 'Indian Rupee',
				'locale'       => 'hi-in',
				'numeric_code' => 356,
			),
			array(
				'code'         => 'IQD',
				'symbol'       => 'IQD',
				'name'         => 'Iraqi Dinar',
				'locale'       => 'ar-iq',
				'numeric_code' => 368,
			),
			array(
				'code'         => 'IRR',
				'symbol'       => 'IRR',
				'name'         => 'Iranian Rial',
				'locale'       => 'fa-ir',
				'numeric_code' => 364,
			),
			array(
				'code'         => 'ISK',
				'symbol'       => 'kr',
				'name'         => "Icelandic Kr\u00f3na",
				'locale'       => 'en-is',
				'numeric_code' => 352,
			),
			array(
				'code'         => 'JMD',
				'symbol'       => '$',
				'name'         => 'Jamaican Dollar',
				'locale'       => 'en-jm',
				'numeric_code' => 388,
			),
			array(
				'code'         => 'JOD',
				'symbol'       => 'JOD',
				'name'         => 'Jordanian Dinar',
				'locale'       => 'ar-jo',
				'numeric_code' => 400,
			),
			array(
				'code'         => 'JPY',
				'symbol'       => "¥",
				'name'         => 'Japanese Yen',
				'locale'       => 'ja-jp',
				'numeric_code' => 392,
			),
			array(
				'code'         => 'KES',
				'symbol'       => 'KES',
				'name'         => 'Kenyan Shilling',
				'locale'       => 'en-ke',
				'numeric_code' => 404,
			),
			array(
				'code'         => 'KGS',
				'symbol'       => 'KGS',
				'name'         => 'Kyrgyzstani Som',
				'locale'       => 'ky-kg',
				'numeric_code' => 417,
			),
			array(
				'code'         => 'KHR',
				'symbol'       => "៛",
				'name'         => 'Cambodian Riel',
				'locale'       => 'km-kh',
				'numeric_code' => 116,
			),
			array(
				'code'         => 'KMF',
				'symbol'       => 'KMF',
				'name'         => 'Comorian Franc',
				'locale'       => 'fr-km',
				'numeric_code' => 174,
			),
			array(
				'code'         => 'KPW',
				'symbol'       => "₩",
				'name'         => 'North Korean Won',
				'locale'       => 'ko-kp',
				'numeric_code' => 408,
			),
			array(
				'code'         => 'KRW',
				'symbol'       => "₩",
				'name'         => 'South Korean Won',
				'locale'       => 'ko-kr',
				'numeric_code' => 410,
			),
			array(
				'code'         => 'KWD',
				'symbol'       => 'KWD',
				'name'         => 'Kuwaiti Dinar',
				'locale'       => 'ar-kw',
				'numeric_code' => 414,
			),
			array(
				'code'         => 'KYD',
				'symbol'       => '$',
				'name'         => 'Cayman Islands Dollar',
				'locale'       => 'en-ky',
				'numeric_code' => 136,
			),
			array(
				'code'         => 'KZT',
				'symbol'       => "₸",
				'name'         => 'Kazakhstani Tenge',
				'locale'       => 'kk-kz',
				'numeric_code' => 398,
			),
			array(
				'code'         => 'LAK',
				'symbol'       => "₭",
				'name'         => 'Lao Kip',
				'locale'       => 'lo-la',
				'numeric_code' => 418,
			),
			array(
				'code'         => 'LBP',
				'symbol'       => "L£",
				'name'         => 'Lebanese Pound',
				'locale'       => 'ar-lb',
				'numeric_code' => 422,
			),
			array(
				'code'         => 'LKR',
				'symbol'       => 'Rs',
				'name'         => 'Sri Lankan Rupee',
				'locale'       => 'si-lk',
				'numeric_code' => 144,
			),
			array(
				'code'         => 'LRD',
				'symbol'       => '$',
				'name'         => 'Liberian Dollar',
				'locale'       => 'en-lr',
				'numeric_code' => 430,
			),
			array(
				'code'         => 'LSL',
				'symbol'       => 'LSL',
				'name'         => 'Lesotho Loti',
				'locale'       => 'en-ls',
				'numeric_code' => 426,
			),
			array(
				'code'         => 'LTL',
				'symbol'       => 'Lt',
				'name'         => 'Lithuanian Litas',
				'locale'       => 'lt-lt',
				'numeric_code' => 440,
			),
			array(
				'code'         => 'LVL',
				'symbol'       => 'LVL',
				'name'         => 'Latvian Lats',
				'locale'       => 'lv-lv',
				'numeric_code' => 428,
			),
			array(
				'code'         => 'LYD',
				'symbol'       => 'LD',
				'name'         => 'Libyan Dinar',
				'locale'       => 'ar-ly',
				'numeric_code' => 434,
			),
			array(
				'code'         => 'MAD',
				'symbol'       => 'MAD',
				'name'         => 'Moroccan Dirham',
				'locale'       => 'ar-ma',
				'numeric_code' => 504,
			),
			array(
				'code'         => 'MDL',
				'symbol'       => 'MDL',
				'name'         => 'Moldovan Leu',
				'locale'       => 'ro-md',
				'numeric_code' => 498,
			),
			array(
				'code'         => 'MGA',
				'symbol'       => 'Ar',
				'name'         => 'Malagasy Ariary',
				'locale'       => 'mg-mg',
				'numeric_code' => 969,
			),
			array(
				'code'         => 'MKD',
				'symbol'       => 'MKD',
				'name'         => 'Macedonian Denar',
				'locale'       => 'mk-mk',
				'numeric_code' => 807,
			),
			array(
				'code'         => 'MMK',
				'symbol'       => 'K',
				'name'         => 'Myanma Kyat',
				'locale'       => 'my-mm',
				'numeric_code' => 104,
			),
			array(
				'code'         => 'MNT',
				'symbol'       => "₮",
				'name'         => 'Mongolian Tugrik',
				'locale'       => 'mn-mn',
				'numeric_code' => 496,
			),
			array(
				'code'         => 'MOP',
				'symbol'       => 'MOP',
				'name'         => 'Macanese Pataca',
				'locale'       => 'pt-mo',
				'numeric_code' => 446,
			),
			array(
				'code'         => 'MRO',
				'symbol'       => 'UM',
				'name'         => 'Mauritanian Ouguiya',
				'locale'       => 'ar-mr',
				'numeric_code' => 478,
			),
			array(
				'code'         => 'MUR',
				'symbol'       => 'Rs',
				'name'         => 'Mauritian Rupee',
				'locale'       => 'en-mu',
				'numeric_code' => 480,
			),
			array(
				'code'         => 'MVR',
				'symbol'       => 'Rf',
				'name'         => 'Maldivian Rufiyaa',
				'locale'       => 'dv-mv',
				'numeric_code' => 462,
			),
			array(
				'code'         => 'MWK',
				'symbol'       => 'MWK',
				'name'         => 'Malawian Kwacha',
				'locale'       => 'en-mw',
				'numeric_code' => 454,
			),
			array(
				'code'         => 'MXN',
				'symbol'       => '$',
				'name'         => 'Mexican Peso',
				'locale'       => 'es-mx',
				'numeric_code' => 484,
			),
			array(
				'code'         => 'MYR',
				'symbol'       => 'RM',
				'name'         => 'Malaysian Ringgit',
				'locale'       => 'ms-my',
				'numeric_code' => 458,
			),
			array(
				'code'         => 'MZN',
				'symbol'       => 'MZN',
				'name'         => 'Mozambican Metical',
				'locale'       => 'pt-mz',
				'numeric_code' => 943,
			),
			array(
				'code'         => 'NAD',
				'symbol'       => '$',
				'name'         => 'Namibian Dollar',
				'locale'       => 'en-na',
				'numeric_code' => 516,
			),
			array(
				'code'         => 'NGN',
				'symbol'       => "₦",
				'name'         => 'Nigerian Naira',
				'locale'       => 'en-ng',
				'numeric_code' => 566,
			),
			array(
				'code'         => 'NIO',
				'symbol'       => '$',
				'name'         => "Nicaraguan C\u00f3rdoba",
				'locale'       => 'es-ni',
				'numeric_code' => 558,
			),
			array(
				'code'         => 'NOK',
				'symbol'       => 'kr',
				'name'         => 'Norwegian Krone',
				'locale'       => 'nb-no',
				'numeric_code' => 578,
			),
			array(
				'code'         => 'NPR',
				'symbol'       => "NPR",
				'name'         => 'Nepalese Rupee',
				'locale'       => 'ne-np',
				'numeric_code' => 524,
			),
			array(
				'code'         => 'NZD',
				'symbol'       => '$',
				'name'         => 'New Zealand Dollar',
				'locale'       => 'en-nz',
				'numeric_code' => 554,
			),
			array(
				'code'         => 'OMR',
				'symbol'       => 'OMR',
				'name'         => 'Omani Rial',
				'locale'       => 'ar-om',
				'numeric_code' => 512,
			),
			array(
				'code'         => 'PAB',
				'symbol'       => 'PAB',
				'name'         => 'Panamanian Balboa',
				'locale'       => 'es-pa',
				'numeric_code' => 590,
			),
			array(
				'code'         => 'PEN',
				'symbol'       => 'PEN',
				'name'         => 'Peruvian Nuevo Sol',
				'locale'       => 'es-pe',
				'numeric_code' => 604,
			),
			array(
				'code'         => 'PGK',
				'symbol'       => 'K',
				'name'         => 'Papua New Guinean Kina',
				'locale'       => 'en-pg',
				'numeric_code' => 598,
			),
			array(
				'code'         => 'PHP',
				'symbol'       => "₱",
				'name'         => 'Philippine Peso',
				'locale'       => 'en-ph',
				'numeric_code' => 608,
			),
			array(
				'code'         => 'PKR',
				'symbol'       => 'Rs',
				'name'         => 'Pakistani Rupee',
				'locale'       => 'en-pk',
				'numeric_code' => 586,
			),
			array(
				'code'         => 'PLN',
				'symbol'       => "zł",
				'name'         => 'Polish Zloty',
				'locale'       => 'pl-pl',
				'numeric_code' => 985,
			),
			array(
				'code'         => 'PYG',
				'symbol'       => "₲",
				'name'         => 'Paraguayan Guarani',
				'locale'       => 'es-py',
				'numeric_code' => 600,
			),
			array(
				'code'         => 'QAR',
				'symbol'       => 'QAR',
				'name'         => 'Qatari Riyal',
				'locale'       => 'ar-qa',
				'numeric_code' => 634,
			),
			array(
				'code'         => 'RON',
				'symbol'       => 'RON',
				'name'         => 'Romanian Leu',
				'locale'       => 'ro-ro',
				'numeric_code' => 946,
			),
			array(
				'code'         => 'RSD',
				'symbol'       => 'RSD',
				'name'         => 'Serbian Dinar',
				'locale'       => 'sr-rs',
				'numeric_code' => 941,
			),
			array(
				'code'         => 'RUB',
				'symbol'       => "₽",
				'name'         => 'Russian Ruble',
				'locale'       => 'ru-ru',
				'numeric_code' => 643,
			),
			array(
				'code'         => 'SAR',
				'symbol'       => 'SAR',
				'name'         => 'Saudi Riyal',
				'locale'       => 'ar-sa',
				'numeric_code' => 682,
			),
			array(
				'code'         => 'SBD',
				'symbol'       => '$',
				'name'         => 'Solomon Islands Dollar',
				'locale'       => 'en-sb',
				'numeric_code' => 90,
			),
			array(
				'code'         => 'SCR',
				'symbol'       => 'Rs',
				'name'         => 'Seychellois Rupee',
				'locale'       => 'en-sc',
				'numeric_code' => 690,
			),
			array(
				'code'         => 'SDG',
				'symbol'       => 'SDG',
				'name'         => 'Sudanese Pound',
				'locale'       => 'ar-sd',
				'numeric_code' => 938,
			),
			array(
				'code'         => 'SEK',
				'symbol'       => 'kr',
				'name'         => 'Swedish Krona',
				'locale'       => 'sv-se',
				'numeric_code' => 752,
			),
			array(
				'code'         => 'SGD',
				'symbol'       => '$',
				'name'         => 'Singapore Dollar',
				'locale'       => 'en-sg',
				'numeric_code' => 702,
			),
			array(
				'code'         => 'SHP',
				'symbol'       => "£",
				'name'         => 'Saint Helena Pound',
				'locale'       => 'en-sh',
				'numeric_code' => 654,
			),
			array(
				'code'         => 'SLL',
				'symbol'       => 'SLL',
				'name'         => 'Sierra Leonean Leone',
				'locale'       => 'en-sl',
				'numeric_code' => 694,
			),
			array(
				'code'         => 'SOS',
				'symbol'       => 'SOS',
				'name'         => 'Somali Shilling',
				'locale'       => 'so-so',
				'numeric_code' => 706,
			),
			array(
				'code'         => 'SRD',
				'symbol'       => '$',
				'name'         => 'Surinamese Dollar',
				'locale'       => 'nl-sr',
				'numeric_code' => 968,
			),
			array(
				'code'         => 'SYP',
				'symbol'       => "£",
				'name'         => 'Syrian Pound',
				'locale'       => 'ar-sy',
				'numeric_code' => 760,
			),
			array(
				'code'         => 'SZL',
				'symbol'       => 'SZL',
				'name'         => 'Swazi Lilangeni',
				'locale'       => 'en-sz',
				'numeric_code' => 748,
			),
			array(
				'code'         => 'THB',
				'symbol'       => "฿",
				'name'         => 'Thai Baht',
				'locale'       => 'th-th',
				'numeric_code' => 764,
			),
			array(
				'code'         => 'TJS',
				'symbol'       => 'TJS',
				'name'         => 'Tajikistani Somoni',
				'locale'       => 'tg-tj',
				'numeric_code' => 972,
			),
			array(
				'code'         => 'TMT',
				'symbol'       => 'm',
				'name'         => 'Turkmen Manat',
				'locale'       => 'tk-tm',
				'numeric_code' => 934,
			),
			array(
				'code'         => 'TND',
				'symbol'       => 'TND',
				'name'         => 'Tunisian Dinar',
				'locale'       => 'ar-tn',
				'numeric_code' => 788,
			),
			array(
				'code'         => 'TRY',
				'symbol'       => 'TRY',
				'name'         => 'Turkish New Lira',
				'locale'       => 'tr-tr',
				'numeric_code' => 949,
			),
			array(
				'code'         => 'TTD',
				'symbol'       => '$',
				'name'         => 'Trinidad and Tobago Dollar',
				'locale'       => 'en-tt',
				'numeric_code' => 780,
			),
			array(
				'code'         => 'TWD',
				'symbol'       => '$',
				'name'         => 'New Taiwan Dollar',
				'locale'       => 'zh-tw',
				'numeric_code' => 901,
			),
			array(
				'code'         => 'TZS',
				'symbol'       => 'TZS',
				'name'         => 'Tanzanian Shilling',
				'locale'       => 'en-tz',
				'numeric_code' => 834,
			),
			array(
				'code'         => 'UAH',
				'symbol'       => 'UAH',
				'name'         => 'Ukrainian Hryvnia',
				'locale'       => 'uk-ua',
				'numeric_code' => 980,
			),
			array(
				'code'         => 'UGX',
				'symbol'       => 'UGX',
				'name'         => 'Ugandan shilling',
				'locale'       => 'en-ug',
				'numeric_code' => 800,
			),
			array(
				'code'         => 'UYU',
				'symbol'       => '$',
				'name'         => 'Uruguayan Peso',
				'locale'       => 'es-uy',
				'numeric_code' => 858,
			),
			array(
				'code'         => 'UZS',
				'symbol'       => 'UZS',
				'name'         => 'Uzbekistani Som',
				'locale'       => 'uz-UZ',
				'numeric_code' => 860,
			),
			array(
				'code'         => 'VES',
				'symbol'       => 'VES',
				'name'         => 'Venezuelan Bolivar',
				'locale'       => 'es-VE',
				'numeric_code' => 928,
			),
			array(
				'code'         => 'VND',
				'symbol'       => "₫",
				'name'         => 'Vietnamese Dong',
				'locale'       => 'vi-VN',
				'numeric_code' => 704,
			),
			array(
				'code'         => 'VUV',
				'symbol'       => 'VT',
				'name'         => 'Vanuatu Vatu',
				'locale'       => 'bi-VU',
				'numeric_code' => 548,
			),
			array(
				'code'         => 'WST',
				'symbol'       => '$',
				'name'         => 'Samoan Tala',
				'locale'       => 'sm-WS',
				'numeric_code' => 882,
			),
			array(
				'code'         => 'XCD',
				'symbol'       => '$',
				'name'         => 'East Caribbean Dollar',
				'locale'       => 'en-XC',
				'numeric_code' => 951,
			),
			array(
				'code'         => 'XDR',
				'symbol'       => 'SDR',
				'name'         => 'Special Drawing Rights',
				'locale'       => 'xdr-XD',
				'numeric_code' => 960,
			),
			array(
				'code'         => 'XOF',
				'symbol'       => 'CFA',
				'name'         => 'West African CFA franc',
				'locale'       => 'fr-XO',
				'numeric_code' => 952,
			),
			array(
				'code'         => 'XPF',
				'symbol'       => 'F',
				'name'         => 'CFP Franc',
				'locale'       => 'fr-PF',
				'numeric_code' => 953,
			),
			array(
				'code'         => 'YER',
				'symbol'       => 'YER',
				'name'         => 'Yemeni Rial',
				'locale'       => 'ar-YE',
				'numeric_code' => 886,
			),
			array(
				'code'         => 'ZAR',
				'symbol'       => 'R',
				'name'         => 'South African Rand',
				'locale'       => 'en-ZA',
				'numeric_code' => 710,
			),
			array(
				'code'         => 'ZMK',
				'symbol'       => 'ZK',
				'name'         => 'Zambian Kwacha',
				'locale'       => 'en-ZM',
				'numeric_code' => 894,
			),
			array(
				'code'         => 'ZWR',
				'symbol'       => '$',
				'name'         => 'Zimbabwean Dollar',
				'locale'       => 'en-ZW',
				'numeric_code' => 935,
			),
		);

		return $currencies;
	}
}

if ( ! function_exists( 'tutor_get_currencies_info_code' ) ) {
	/**
	 * Get tutor currencies
	 *
	 * @since 3.0.0
	 *
	 * @param string $code Currency code.
	 *
	 * @return array
	 */
	function tutor_get_currencies_info_by_code( $code ) {
		$currencies = get_tutor_currencies();
		$found      = null;

		foreach ( $currencies as $currency ) {
			$flip = array_flip( $currency );
			if ( isset( $flip[ $code ] ) ) {
				$found = $currency;
				break;
			}
		}
		return $found;
	}
}

if ( ! function_exists( 'get_currency_symbol_by_code' ) ) {
	/**
	 * Get currency options where key is symbol
	 * and code is value
	 *
	 * It will return $ as default
	 *
	 * @since 3.0.0
	 *
	 * @param mixed $code Currency code.
	 *
	 * @return string
	 */
	function tutor_get_currency_symbol_by_code( $code ) {
		$currencies = get_tutor_currencies();
		$search     = array_search( $code, array_column( $currencies, 'code' ) );

		if ( false !== $search ) {
			return $currencies[ $search ]['symbol'];
		} else {
			return '$';
		}
	}
}




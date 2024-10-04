<?php
/**
 * Manage all the cart items for the checkout
 *
 * @package Tutor\Ecommerce
 * @author Themeum
 * @link https://themeum.com
 * @since 3.0.0
 */

namespace Tutor\Ecommerce\Constants;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store the country codes to constant variables.
 *
 * @since 3.0.0
 */
final class CountryCodes {
	// EU COUNTRIES
	public const AUSTRIA        = '040';
	public const BELGIUM        = '056';
	public const BULGARIA       = '100';
	public const CROATIA        = '191';
	public const CYPRUS         = '196';
	public const CZECH_REPUBLIC = '203';
	public const DENMARK        = '208';
	public const ESTONIA        = '233';
	public const FINLAND        = '246';
	public const FRANCE         = '250';
	public const GERMANY        = '276';
	public const GREECE         = '300';
	public const HUNGARY        = '348';
	public const IRELAND        = '372';
	public const ITALY          = '380';
	public const LATVIA         = '428';
	public const LITHUANIA      = '440';
	public const LUXEMBOURG     = '442';
	public const MALTA          = '470';
	public const NETHERLANDS    = '528';
	public const POLAND         = '616';
	public const PORTUGAL       = '620';
	public const ROMANIA        = '642';
	public const SLOVAKIA       = '703';
	public const SLOVENIA       = '705';
	public const SPAIN          = '724';
	public const SWEDEN         = '752';
	public const EUROPEAN_UNION = '000';

	// OTHER COUNTRIES
	public const AFGHANISTAN                          = '004';
	public const ALAND_ISLANDS                        = '248';
	public const ALBANIA                              = '008';
	public const ALGERIA                              = '012';
	public const AMERICAN_SAMOA                       = '016';
	public const ANDORRA                              = '020';
	public const ANGOLA                               = '024';
	public const ANGUILLA                             = '660';
	public const ANTARCTICA                           = '010';
	public const ANTIGUA_AND_BARBUDA                  = '028';
	public const ARGENTINA                            = '032';
	public const ARMENIA                              = '051';
	public const ARUBA                                = '533';
	public const AUSTRALIA                            = '036';
	public const AZERBAIJAN                           = '031';
	public const BAHRAIN                              = '048';
	public const BANGLADESH                           = '050';
	public const BARBADOS                             = '052';
	public const BELARUS                              = '112';
	public const BELIZE                               = '084';
	public const BENIN                                = '204';
	public const BERMUDA                              = '060';
	public const BHUTAN                               = '064';
	public const BOLIVIA                              = '068';
	public const BONAIRE_SINT_EUSTATIUS_AND_SABA      = '535';
	public const BOSNIA_AND_HERZEGOVINA               = '070';
	public const BOTSWANA                             = '072';
	public const BOUVET_ISLAND                        = '074';
	public const BRAZIL                               = '076';
	public const BRITISH_INDIAN_OCEAN_TERRITORY       = '086';
	public const BRUNEI                               = '096';
	public const BURKINA_FASO                         = '854';
	public const BURUNDI                              = '108';
	public const CAMBODIA                             = '116';
	public const CAMEROON                             = '120';
	public const CANADA                               = '124';
	public const CAPE_VERDE                           = '132';
	public const CAYMAN_ISLANDS                       = '136';
	public const CENTRAL_AFRICAN_REPUBLIC             = '140';
	public const CHAD                                 = '148';
	public const CHILE                                = '152';
	public const CHINA                                = '156';
	public const CHRISTMAS_ISLAND                     = '162';
	public const COCOS_KEELING_ISLANDS                = '166';
	public const COLOMBIA                             = '170';
	public const COMOROS                              = '174';
	public const CONGO                                = '178';
	public const COOK_ISLANDS                         = '184';
	public const COSTA_RICA                           = '188';
	public const COTE_DIVOIRE_IVORY_COAST             = '384';
	public const CUBA                                 = '192';
	public const CURAçAO                              = '531';
	public const DEMOCRATIC_REPUBLIC_OF_THE_CONGO     = '180';
	public const DJIBOUTI                             = '262';
	public const DOMINICA                             = '212';
	public const DOMINICAN_REPUBLIC                   = '214';
	public const EAST_TIMOR                           = '626';
	public const ECUADOR                              = '218';
	public const EGYPT                                = '818';
	public const EL_SALVADOR                          = '222';
	public const EQUATORIAL_GUINEA                    = '226';
	public const ERITREA                              = '232';
	public const ETHIOPIA                             = '231';
	public const FALKLAND_ISLANDS                     = '238';
	public const FAROE_ISLANDS                        = '234';
	public const FIJI_ISLANDS                         = '242';
	public const FRENCH_GUIANA                        = '254';
	public const FRENCH_POLYNESIA                     = '258';
	public const FRENCH_SOUTHERN_TERRITORIES          = '260';
	public const GABON                                = '266';
	public const GAMBIA_THE                           = '270';
	public const GEORGIA                              = '268';
	public const GHANA                                = '288';
	public const GIBRALTAR                            = '292';
	public const GREENLAND                            = '304';
	public const GRENADA                              = '308';
	public const GUADELOUPE                           = '312';
	public const GUAM                                 = '316';
	public const GUATEMALA                            = '320';
	public const GUERNSEY_AND_ALDERNEY                = '831';
	public const GUINEA                               = '324';
	public const GUINEA_BISSAU                        = '624';
	public const GUYANA                               = '328';
	public const HAITI                                = '332';
	public const HEARD_ISLAND_AND_MCDONALD_ISLANDS    = '334';
	public const HONDURAS                             = '340';
	public const HONG_KONG_SAR                        = '344';
	public const ICELAND                              = '352';
	public const INDIA                                = '356';
	public const INDONESIA                            = '360';
	public const IRAN                                 = '364';
	public const IRAQ                                 = '368';
	public const ISRAEL                               = '376';
	public const JAMAICA                              = '388';
	public const JAPAN                                = '392';
	public const JERSEY                               = '832';
	public const JORDAN                               = '400';
	public const KAZAKHSTAN                           = '398';
	public const KENYA                                = '404';
	public const KIRIBATI                             = '296';
	public const KOSOVO                               = '926';
	public const KUWAIT                               = '414';
	public const KYRGYZSTAN                           = '417';
	public const LAOS                                 = '418';
	public const LEBANON                              = '422';
	public const LESOTHO                              = '426';
	public const LIBERIA                              = '430';
	public const LIBYA                                = '434';
	public const LIECHTENSTEIN                        = '438';
	public const MACAU_SAR                            = '446';
	public const MACEDONIA                            = '807';
	public const MADAGASCAR                           = '450';
	public const MALAWI                               = '454';
	public const MALAYSIA                             = '458';
	public const MALDIVES                             = '462';
	public const MALI                                 = '466';
	public const MAN_ISLE_OF                          = '833';
	public const MARSHALL_ISLANDS                     = '584';
	public const MARTINIQUE                           = '474';
	public const MAURITANIA                           = '478';
	public const MAURITIUS                            = '480';
	public const MAYOTTE                              = '175';
	public const MEXICO                               = '484';
	public const MICRONESIA                           = '583';
	public const MOLDOVA                              = '498';
	public const MONACO                               = '492';
	public const MONGOLIA                             = '496';
	public const MONTENEGRO                           = '499';
	public const MONTSERRAT                           = '500';
	public const MOROCCO                              = '504';
	public const MOZAMBIQUE                           = '508';
	public const MYANMAR                              = '104';
	public const NAMIBIA                              = '516';
	public const NAURU                                = '520';
	public const NEPAL                                = '524';
	public const NEW_CALEDONIA                        = '540';
	public const NEW_ZEALAND                          = '554';
	public const NICARAGUA                            = '558';
	public const NIGER                                = '562';
	public const NIGERIA                              = '566';
	public const NIUE                                 = '570';
	public const NORFOLK_ISLAND                       = '574';
	public const NORTH_KOREA                          = '408';
	public const NORTHERN_MARIANA_ISLANDS             = '580';
	public const NORWAY                               = '578';
	public const OMAN                                 = '512';
	public const PAKISTAN                             = '586';
	public const PALAU                                = '585';
	public const PALESTINIAN_TERRITORY_OCCUPIED       = '275';
	public const PANAMA                               = '591';
	public const PAPUA_NEW_GUINEA                     = '598';
	public const PARAGUAY                             = '600';
	public const PERU                                 = '604';
	public const PHILIPPINES                          = '608';
	public const PITCAIRN_ISLAND                      = '612';
	public const PUERTO_RICO                          = '630';
	public const QATAR                                = '634';
	public const REUNION                              = '638';
	public const RUSSIA                               = '643';
	public const RWANDA                               = '646';
	public const SAINT_HELENA                         = '654';
	public const SAINT_KITTS_AND_NEVIS                = '659';
	public const SAINT_LUCIA                          = '662';
	public const SAINT_PIERRE_AND_MIQUELON            = '666';
	public const SAINT_VINCENT_AND_THE_GRENADINES     = '670';
	public const SAINT_BARTHELEMY                     = '652';
	public const SAINT_MARTIN_FRENCH_PART             = '663';
	public const SAMOA                                = '882';
	public const SAN_MARINO                           = '674';
	public const SAO_TOME_AND_PRINCIPE                = '678';
	public const SAUDI_ARABIA                         = '682';
	public const SENEGAL                              = '686';
	public const SERBIA                               = '688';
	public const SEYCHELLES                           = '690';
	public const SIERRA_LEONE                         = '694';
	public const SINGAPORE                            = '702';
	public const SINT_MAARTEN_DUTCH_PART              = '534';
	public const SOLOMON_ISLANDS                      = '090';
	public const SOMALIA                              = '706';
	public const SOUTH_AFRICA                         = '710';
	public const SOUTH_GEORGIA                        = '239';
	public const SOUTH_KOREA                          = '410';
	public const SOUTH_SUDAN                          = '728';
	public const SRI_LANKA                            = '144';
	public const SUDAN                                = '729';
	public const SURINAME                             = '740';
	public const SVALBARD_AND_JAN_MAYEN_ISLANDS       = '744';
	public const SWAZILAND                            = '748';
	public const SWITZERLAND                          = '756';
	public const SYRIA                                = '760';
	public const TAIWAN                               = '158';
	public const TAJIKISTAN                           = '762';
	public const TANZANIA                             = '834';
	public const THAILAND                             = '764';
	public const THE_BAHAMAS                          = '044';
	public const TOGO                                 = '768';
	public const TOKELAU                              = '772';
	public const TONGA                                = '776';
	public const TRINIDAD_AND_TOBAGO                  = '780';
	public const TUNISIA                              = '788';
	public const TURKEY                               = '792';
	public const TURKMENISTAN                         = '795';
	public const TURKS_AND_CAICOS_ISLANDS             = '796';
	public const TUVALU                               = '798';
	public const UGANDA                               = '800';
	public const UKRAINE                              = '804';
	public const UNITED_ARAB_EMIRATES                 = '784';
	public const UNITED_KINGDOM                       = '826';
	public const UNITED_STATES                        = '840';
	public const UNITED_STATES_MINOR_OUTLYING_ISLANDS = '581';
	public const URUGUAY                              = '858';
	public const UZBEKISTAN                           = '860';
	public const VANUATU                              = '548';
	public const VATICAN_CITY_STATE_HOLY_SEE          = '336';
	public const VENEZUELA                            = '862';
	public const VIETNAM                              = '704';
	public const VIRGIN_ISLANDS_BRITISH               = '092';
	public const VIRGIN_ISLANDS_US                    = '850';
	public const WALLIS_AND_FUTUNA_ISLANDS            = '876';
	public const WESTERN_SAHARA                       = '732';
	public const YEMEN                                = '887';
	public const ZAMBIA                               = '894';
	public const ZIMBABWE                             = '716';

	/**
	 * Get the numeric codes from the alpha_2 code.
	 *
	 * @return array<string, string>
	 * @since 1.3.0
	 */
	public static function getCountryCodes() {
		return array(
			'AF' => '004',
			'AX' => '248',
			'AL' => '008',
			'DZ' => '012',
			'AS' => '016',
			'AD' => '020',
			'AO' => '024',
			'AI' => '660',
			'AQ' => '010',
			'AG' => '028',
			'AR' => '032',
			'AM' => '051',
			'AW' => '533',
			'AU' => '036',
			'AT' => '040',
			'AZ' => '031',
			'BH' => '048',
			'BD' => '050',
			'BB' => '052',
			'BY' => '112',
			'BE' => '056',
			'BZ' => '084',
			'BJ' => '204',
			'BM' => '060',
			'BT' => '064',
			'BO' => '068',
			'BQ' => '535',
			'BA' => '070',
			'BW' => '072',
			'BV' => '074',
			'BR' => '076',
			'IO' => '086',
			'BN' => '096',
			'BG' => '100',
			'BF' => '854',
			'BI' => '108',
			'KH' => '116',
			'CM' => '120',
			'CA' => '124',
			'CV' => '132',
			'KY' => '136',
			'CF' => '140',
			'TD' => '148',
			'CL' => '152',
			'CN' => '156',
			'CX' => '162',
			'CC' => '166',
			'CO' => '170',
			'KM' => '174',
			'CG' => '178',
			'CK' => '184',
			'CR' => '188',
			'CI' => '384',
			'HR' => '191',
			'CU' => '192',
			'CW' => '531',
			'CY' => '196',
			'CZ' => '203',
			'CD' => '180',
			'DK' => '208',
			'DJ' => '262',
			'DM' => '212',
			'DO' => '214',
			'TL' => '626',
			'EC' => '218',
			'EG' => '818',
			'SV' => '222',
			'GQ' => '226',
			'ER' => '232',
			'EE' => '233',
			'ET' => '231',
			'FK' => '238',
			'FO' => '234',
			'FJ' => '242',
			'FI' => '246',
			'FR' => '250',
			'GF' => '254',
			'PF' => '258',
			'TF' => '260',
			'GA' => '266',
			'GM' => '270',
			'GE' => '268',
			'DE' => '276',
			'GH' => '288',
			'GI' => '292',
			'GR' => '300',
			'GL' => '304',
			'GD' => '308',
			'GP' => '312',
			'GU' => '316',
			'GT' => '320',
			'GG' => '831',
			'GN' => '324',
			'GW' => '624',
			'GY' => '328',
			'HT' => '332',
			'HM' => '334',
			'HN' => '340',
			'HK' => '344',
			'HU' => '348',
			'IS' => '352',
			'IN' => '356',
			'ID' => '360',
			'IR' => '364',
			'IQ' => '368',
			'IE' => '372',
			'IL' => '376',
			'IT' => '380',
			'JM' => '388',
			'JP' => '392',
			'JE' => '832',
			'JO' => '400',
			'KZ' => '398',
			'KE' => '404',
			'KI' => '296',
			'XK' => '926',
			'KW' => '414',
			'KG' => '417',
			'LA' => '418',
			'LV' => '428',
			'LB' => '422',
			'LS' => '426',
			'LR' => '430',
			'LY' => '434',
			'LI' => '438',
			'LT' => '440',
			'LU' => '442',
			'MO' => '446',
			'MK' => '807',
			'MG' => '450',
			'MW' => '454',
			'MY' => '458',
			'MV' => '462',
			'ML' => '466',
			'MT' => '470',
			'IM' => '833',
			'MH' => '584',
			'MQ' => '474',
			'MR' => '478',
			'MU' => '480',
			'YT' => '175',
			'MX' => '484',
			'FM' => '583',
			'MD' => '498',
			'MC' => '492',
			'MN' => '496',
			'ME' => '499',
			'MS' => '500',
			'MA' => '504',
			'MZ' => '508',
			'MM' => '104',
			'NA' => '516',
			'NR' => '520',
			'NP' => '524',
			'NL' => '528',
			'NC' => '540',
			'NZ' => '554',
			'NI' => '558',
			'NE' => '562',
			'NG' => '566',
			'NU' => '570',
			'NF' => '574',
			'KP' => '408',
			'MP' => '580',
			'NO' => '578',
			'OM' => '512',
			'PK' => '586',
			'PW' => '585',
			'PS' => '275',
			'PA' => '591',
			'PG' => '598',
			'PY' => '600',
			'PE' => '604',
			'PH' => '608',
			'PN' => '612',
			'PL' => '616',
			'PT' => '620',
			'PR' => '630',
			'QA' => '634',
			'RE' => '638',
			'RO' => '642',
			'RU' => '643',
			'RW' => '646',
			'SH' => '654',
			'KN' => '659',
			'LC' => '662',
			'PM' => '666',
			'VC' => '670',
			'BL' => '652',
			'MF' => '663',
			'WS' => '882',
			'SM' => '674',
			'ST' => '678',
			'SA' => '682',
			'SN' => '686',
			'RS' => '688',
			'SC' => '690',
			'SL' => '694',
			'SG' => '702',
			'SX' => '534',
			'SK' => '703',
			'SI' => '705',
			'SB' => '090',
			'SO' => '706',
			'ZA' => '710',
			'GS' => '239',
			'KR' => '410',
			'SS' => '728',
			'ES' => '724',
			'LK' => '144',
			'SD' => '729',
			'SR' => '740',
			'SJ' => '744',
			'SZ' => '748',
			'SE' => '752',
			'CH' => '756',
			'SY' => '760',
			'TW' => '158',
			'TJ' => '762',
			'TZ' => '834',
			'TH' => '764',
			'BS' => '044',
			'TG' => '768',
			'TK' => '772',
			'TO' => '776',
			'TT' => '780',
			'TN' => '788',
			'TR' => '792',
			'TM' => '795',
			'TC' => '796',
			'TV' => '798',
			'UG' => '800',
			'UA' => '804',
			'AE' => '784',
			'GB' => '826',
			'US' => '840',
			'UM' => '581',
			'UY' => '858',
			'UZ' => '860',
			'VU' => '548',
			'VA' => '336',
			'VE' => '862',
			'VN' => '704',
			'VG' => '092',
			'VI' => '850',
			'WF' => '876',
			'EH' => '732',
			'YE' => '887',
			'ZM' => '894',
			'ZW' => '716',
		);
	}

	public static function getStateLessCountries() {
		return array(
			self::ALAND_ISLANDS,
			self::AMERICAN_SAMOA,
			self::ANGUILLA,
			self::ANTARCTICA,
			self::ARUBA,
			self::BOUVET_ISLAND,
			self::BRITISH_INDIAN_OCEAN_TERRITORY,
			self::CAYMAN_ISLANDS,
			self::CHRISTMAS_ISLAND,
			self::COCOS_KEELING_ISLANDS,
			self::COOK_ISLANDS,
			self::CURAçAO,
			self::FALKLAND_ISLANDS,
			self::FAROE_ISLANDS,
			self::FRENCH_GUIANA,
			self::FRENCH_POLYNESIA,
			self::FRENCH_SOUTHERN_TERRITORIES,
			self::GIBRALTAR,
			self::GREENLAND,
			self::GUADELOUPE,
			self::GUAM,
			self::GUERNSEY_AND_ALDERNEY,
			self::HEARD_ISLAND_AND_MCDONALD_ISLANDS,
			self::JERSEY,
			self::MACAU_SAR,
			self::MAN_ISLE_OF,
			self::MARTINIQUE,
			self::MAYOTTE,
			self::MONTSERRAT,
			self::NEW_CALEDONIA,
			self::NIUE,
			self::NORFOLK_ISLAND,
			self::NORTHERN_MARIANA_ISLANDS,
			self::PALESTINIAN_TERRITORY_OCCUPIED,
			self::PITCAIRN_ISLAND,
			self::REUNION,
			self::SAINT_HELENA,
			self::SAINT_PIERRE_AND_MIQUELON,
			self::SAINT_BARTHELEMY,
			self::SAINT_MARTIN_FRENCH_PART,
			self::SINT_MAARTEN_DUTCH_PART,
			self::SOUTH_GEORGIA,
			self::SVALBARD_AND_JAN_MAYEN_ISLANDS,
			self::TOKELAU,
			self::TURKS_AND_CAICOS_ISLANDS,
			self::UNITED_STATES_MINOR_OUTLYING_ISLANDS,
			self::VATICAN_CITY_STATE_HOLY_SEE,
			self::VIRGIN_ISLANDS_BRITISH,
			self::WALLIS_AND_FUTUNA_ISLANDS,
			self::WESTERN_SAHARA,
		);
	}
}

<?php

class UserAttributeModel extends ModelBase
{    
    public $experienceList = array(
            0,  //0级
            116000,//1级
            233000,
            350000,
            466000,
            700000,
            933000,
            1166000,
            1400000,
            1750000,
            2100000,//10级
            2450000,
            2800000,
            3266000,
            3733000,
            4200000,
            4666000,
            5180000,
            5693000,
            6206000,
            6720000,//20级
            7280000,
            7840000,
            8400000,
            8960000,
            9566000,
            10173000,
            10780000,
            11386000,
            12040000,
            12693000,//30级
            13346000,
            14000000,
            14700000,
            15400000,
            16100000,
            16800000,
            17546000,
            18293000,
            19040000,
            19786000,//40级
            20580000,
            21373000,
            22166000,
            22960000,
            23800000,
            24640000,
            25480000,
            26320000,
            27253000,
            28373000,//50级
            29680000,
            31173000,
            32853000,
            34720000,
            36773000,
            39013000,
            41440000,
            44053000,
            46853000,
            49840000,//60级
            53013000,
            56373000,
            59920000,
            63653000,
            67573000,
            71680000,
            75973000,
            80453000,
            85120000,
            89973000,//70级
            95013000,
            100240000,
            105653000,
            111253000,
            117040000,
            123013000,
            129173000,
            135520000,
            142053000,
            148773000,//80级
            155680000,
            162773000,
            170053000,
            177520000,
            185173000,
            193013000,
            201040000,
            209253000,
            217653000,
            226240000,//90级
            235013000,
            243973000,
            253120000,
            262453000,
            271973000,
            281680000,
            291573000,
            301653000,
            311920000,
            322373000,//100级
            333013000,
            343840000,
            354853000,
            366053000,
            377440000,
            389013000,
            400773000,
            412720000,
            424853000,
            437173000,//110级
            449680000,
            462373000,
            475253000,
            488320000,
            501573000,
            515013000,
            528640000,
            542453000,
            556453000,
            570640000,//120级
            585013000,
            599573000,
            614320000,
            629253000,
            644373000,
            659680000,
            675173000,
            690853000,
            706720000,
            722773000,//130级
            739013000,
            755440000,
            772053000,
            788853000,
            805840000,
            823013000,
            840373000,
            857920000,
            875653000,
            893573000,//140级
            911680000,
            929973000,
            948453000,
            967120000,
            985973000,
            1005013000,
            1024240000,
            1043653000,
            9999999000 //149级
    );

    public $weekGiftConsumeList = array(
        20000,
        50000,
        100000,
        200000,
        500000,
        1000000,
        5000000
    );

    public $vipList = array(
        array(
            'vipTitle' => '',
            'vip' => 0,
            'vipPrice' => 0,
            'giftDiscount' => 0,
            'speakerPrice' => 50
        ),
        array(
            'vipTitle' => 'VIP1',
            'vip' => 1,
            'vipPrice' => 10000,
            'giftDiscount' => 0.9,
            'speakerPrice' => 20
        ),
        array(
            'vipTitle' => 'VIP2',
            'vip' => 2,
            'vipPrice' => 20000,
            'giftDiscount' => 0.9,
            'speakerPrice' => 10
        ),
        array(
            'vipTitle' => 'VIP3',
            'vip' => 3,
            'vipPrice' => 30000,
            'giftDiscount' => 0.9,
            'speakerPrice' => 0
        ),
        array(
            'vipTitle' => 'VIP4',
            'vip' => 4,
            'vipPrice' => 40000,
            'giftDiscount' => 0.9,
            'speakerPrice' => 0
        ),
        array(
            'vipTitle' => 'VIP5',
            'vip' => 5,
            'vipPrice' => 50000,
            'giftDiscount' => 0.9,
            'speakerPrice' => 0
        ),
        array(
            'vipTitle' => 'VIP6',
            'vip' => 6,
            'vipPrice' => 60000,
            'giftDiscount' => 0.9,
            'speakerPrice' => 0
        )
    );

    public $giftConsumeList = array(
        array(
            'level' => 0,
            'title' => '平民',
            'amount' => 0,
            'boxid' => 5,
            'is_all' => 0
        ),
        array(
            'level' => 0,
            'title' => '平民',
            'amount' => 1000,
            'boxid' => 5,
            'is_all' => 0
        ),
        array(
            'level' => 1,
            'title' => '一壕',
            'amount' => 3000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 2,
            'title' => '二壕',
            'amount' => 5000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 3,
            'title' => '三壕',
            'amount' => 8000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 4,
            'title' => '四壕',
            'amount' => 13000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 5,
            'title' => '五壕',
            'amount' => 20000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 6,
            'title' => '六壕',
            'amount' => 30000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 7,
            'title' => '七壕',
            'amount' => 45000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 8,
            'title' => '八壕',
            'amount' => 70000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 9,
            'title' => '九壕',
            'amount' => 100000,
            'boxid' => 8,
            'is_all' => 1
        ),
        array(
            'level' => 10,
            'titile' => '1星男爵',
            'amount' => 130000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 11,
            'titile' => '2星男爵',
            'amount' => 165000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 12,
            'titile' => '3星男爵',
            'amount' => 200000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 13,
            'titile' => '4星男爵',
            'amount' => 240000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 14,
            'titile' => '5星男爵',
            'amount' => 300000,
            'boxid' => 9,
            'is_all' => 1
        ),
        array(
            'level' => 15,
            'titile' => '1星子爵',
            'amount' => 380000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 16,
            'titile' => '2星子爵',
            'amount' => 480000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 17,
            'titile' => '3星子爵',
            'amount' => 600000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 18,
            'titile' => '4星子爵',
            'amount' => 780000,
            'boxid' => 0,
            'is_all' => 0
        ),
        array(
            'level' => 19,
            'titile' => '5星子爵',
            'amount' => 1000000,
            'boxid' => 11,
            'is_all' => 1
        ),
        array(
            'level' => 20,
            'titile' => '1星伯爵',
            'amount' => 1300000,
            'boxid' => 11,
            'is_all' => 1
        ),
        array(
            'level' => 21,
            'titile' => '2星伯爵',
            'amount' => 1600000,
            'boxid' => 11,
            'is_all' => 1
        ),
        array(
            'level' => 22,
            'titile' => '3星伯爵',
            'amount' => 2000000,
            'boxid' => 11,
            'is_all' => 1
        ),
        array(
            'level' => 23,
            'titile' => '4星伯爵',
            'amount' => 2500000,
            'boxid' => 11,
            'is_all' => 1
        ),
        array(
            'level' => 24,
            'titile' => '5星伯爵',
            'amount' => 3000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 25,
            'titile' => '1星侯爵',
            'amount' => 3700000,
            'boxid' => 11,
            'is_all' => 1
        ),
        array(
            'level' => 26,
            'titile' => '2星侯爵',
            'amount' => 4700000,
            'boxid' => 11,
            'is_all' => 1
        ),
        array(
            'level' => 27,
            'titile' => '3星侯爵',
            'amount' => 6000000,
            'boxid' => 11,
            'is_all' => 1
        ),
        array(
            'level' => 28,
            'titile' => '4星侯爵',
            'amount' => 8000000,
            'boxid' => 11,
            'is_all' => 1
        ),
        array(
            'level' => 29,
            'titile' => '5星侯爵',
            'amount' => 10000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 30,
            'titile' => '1星公爵',
            'amount' => 12000000,
            'boxid' => 12,
            'is_all' => 1
        ),
        array(
            'level' => 31,
            'titile' => '2星公爵',
            'amount' => 14000000,
            'boxid' => 12,
            'is_all' => 1
        ),
        array(
            'level' => 32,
            'titile' => '3星公爵',
            'amount' => 16000000,
            'boxid' => 12,
            'is_all' => 1
        ),
        array(
            'level' => 33,
            'titile' => '4星公爵',
            'amount' => 18000000,
            'boxid' => 12,
            'is_all' => 1
        ),
        array(
            'level' => 34,
            'titile' => '5星公爵',
            'amount' => 20000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 35,
            'titile' => '单珠郡王',
            'amount' => 23000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 36,
            'titile' => '双珠郡王',
            'amount' => 26000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 37,
            'titile' => '三珠郡王',
            'amount' => 30000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 38,
            'titile' => '四珠郡王',
            'amount' => 35000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 39,
            'titile' => '五珠郡王',
            'amount' => 40000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 40,
            'titile' => '单珠亲王',
            'amount' => 46000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 41,
            'titile' => '双珠亲王',
            'amount' => 52000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 42,
            'titile' => '三珠亲王',
            'amount' => 58000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 43,
            'titile' => '四珠亲王',
            'amount' => 66000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 44,
            'titile' => '五珠亲王',
            'amount' => 75000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 45,
            'titile' => '国王',
            'amount' => 85000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 46,
            'titile' => '君王',
            'amount' => 100000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 47,
            'titile' => '帝王1',
            'amount' => 120000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 48,
            'titile' => '帝王2',
            'amount' => 150000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 49,
            'titile' => '帝王3',
            'amount' => 188000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 50,
            'titile' => '帝王4',
            'amount' => 240000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 51,
            'titile' => '帝王5',
            'amount' => 310000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 52,
            'titile' => '帝王6',
            'amount' => 400000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 53,
            'titile' => '帝王7',
            'amount' => 500000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 54,
            'titile' => '帝王8',
            'amount' => 600000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 55,
            'titile' => '帝王9',
            'amount' => 715000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 56,
            'titile' => '帝王10',
            'amount' => 835000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 57,
            'titile' => '帝王11',
            'amount' => 1000000000,
            'boxid' => 13,
            'is_all' => 1
        ),
        array(
            'level' => 58,
            'titile' => '帝王12',
            'amount' => 99999999999
        )
    );
    
    // 活跃等级列表
    public $activeLevelList = array(
        array(
            'amount' => 0,
            'level' => 1
        ),
        array(
            'level' => 1,
            'amount' => 12
        ),
        array(
            'level' => 2,
            'amount' => 28
        ),
        array(
            'level' => 3,
            'amount' => 48
        ),
        array(
            'level' => 4,
            'amount' => 72
        ),
        array(
            'level' => 5,
            'amount' => 100
        ),
        array(
            'level' => 6,
            'amount' => 132
        ),
        array(
            'level' => 7,
            'amount' => 168
        ),
        array(
            'level' => 8,
            'amount' => 208
        ),
        array(
            'level' => 9,
            'amount' => 252
        ),
        array(
            'level' => 10,
            'amount' => 300
        ),
        array(
            'level' => 11,
            'amount' => 352
        ),
        array(
            'level' => 12,
            'amount' => 408
        ),
        array(
            'level' => 13,
            'amount' => 468
        ),
        array(
            'level' => 14,
            'amount' => 532
        ),
        array(
            'level' => 15,
            'amount' => 600
        ),
        array(
            'level' => 16,
            'amount' => 672
        ),
        array(
            'level' => 17,
            'amount' => 748
        ),
        array(
            'level' => 18,
            'amount' => 828
        ),
        array(
            'level' => 19,
            'amount' => 912
        ),
        array(
            'level' => 20,
            'amount' => 1000
        ),
        array(
            'level' => 21,
            'amount' => 1100
        ),
        array(
            'level' => 22,
            'amount' => 1208
        ),
        array(
            'level' => 23,
            'amount' => 1324
        ),
        array(
            'level' => 24,
            'amount' => 1448
        ),
        array(
            'level' => 25,
            'amount' => 1580
        ),
        array(
            'level' => 26,
            'amount' => 1720
        ),
        array(
            'level' => 27,
            'amount' => 1868
        ),
        array(
            'level' => 28,
            'amount' => 2024
        ),
        array(
            'level' => 29,
            'amount' => 2188
        ),
        array(
            'level' => 30,
            'amount' => 2360
        ),
        array(
            'level' => 31,
            'amount' => 2540
        ),
        array(
            'level' => 32,
            'amount' => 2728
        ),
        array(
            'level' => 33,
            'amount' => 2924
        ),
        array(
            'level' => 34,
            'amount' => 3128
        ),
        array(
            'level' => 35,
            'amount' => 3340
        ),
        array(
            'level' => 36,
            'amount' => 3560
        ),
        array(
            'level' => 37,
            'amount' => 3788
        ),
        array(
            'level' => 38,
            'amount' => 4024
        ),
        array(
            'level' => 39,
            'amount' => 4268
        ),
        array(
            'level' => 40,
            'amount' => 4520
        ),
        array(
            'level' => 41,
            'amount' => 4780
        ),
        array(
            'level' => 42,
            'amount' => 5048
        ),
        array(
            'level' => 43,
            'amount' => 5324
        ),
        array(
            'level' => 44,
            'amount' => 5608
        ),
        array(
            'level' => 45,
            'amount' => 5900
        ),
        array(
            'level' => 46,
            'amount' => 6200
        ),
        array(
            'level' => 47,
            'amount' => 6508
        ),
        array(
            'level' => 48,
            'amount' => 6824
        ),
        array(
            'level' => 49,
            'amount' => 7148
        ),
        array(
            'level' => 50,
            'amount' => 7480
        ),
        array(
            'level' => 51,
            'amount' => 7820
        ),
        array(
            'level' => 52,
            'amount' => 8168
        ),
        array(
            'level' => 53,
            'amount' => 8524
        ),
        array(
            'level' => 54,
            'amount' => 8888
        ),
        array(
            'level' => 55,
            'amount' => 9260
        ),
        array(
            'level' => 56,
            'amount' => 9640
        ),
        array(
            'level' => 57,
            'amount' => 10028
        ),
        array(
            'level' => 58,
            'amount' => 10424
        ),
        array(
            'level' => 59,
            'amount' => 10828
        ),
        array(
            'level' => 60,
            'amount' => 11240
        ),
        array(
            'level' => 61,
            'amount' => 11660
        ),
        array(
            'level' => 62,
            'amount' => 12090
        ),
        array(
            'level' => 63,
            'amount' => 12530
        ),
        array(
            'level' => 64,
            'amount' => 12980
        ),
        array(
            'level' => 65,
            'amount' => 13440
        ),
        array(
            'level' => 66,
            'amount' => 13910
        ),
        array(
            'level' => 67,
            'amount' => 14390
        ),
        array(
            'level' => 68,
            'amount' => 14880
        ),
        array(
            'level' => 69,
            'amount' => 15380
        ),
        array(
            'level' => 70,
            'amount' => 15890
        ),
        array(
            'level' => 71,
            'amount' => 16410
        ),
        array(
            'level' => 72,
            'amount' => 16940
        ),
        array(
            'level' => 73,
            'amount' => 17480
        ),
        array(
            'level' => 74,
            'amount' => 18030
        ),
        array(
            'level' => 75,
            'amount' => 18590
        ),
        array(
            'level' => 76,
            'amount' => 19160
        ),
        array(
            'level' => 77,
            'amount' => 19740
        ),
        array(
            'level' => 78,
            'amount' => 20330
        ),
        array(
            'level' => 79,
            'amount' => 20930
        ),
        array(
            'level' => 80,
            'amount' => 21540
        ),
        array(
            'level' => 81,
            'amount' => 22160
        ),
        array(
            'level' => 82,
            'amount' => 22791
        ),
        array(
            'level' => 83,
            'amount' => 23433
        ),
        array(
            'level' => 84,
            'amount' => 24086
        ),
        array(
            'level' => 85,
            'amount' => 24750
        ),
        array(
            'level' => 86,
            'amount' => 25425
        ),
        array(
            'level' => 87,
            'amount' => 26111
        ),
        array(
            'level' => 88,
            'amount' => 26808
        ),
        array(
            'level' => 89,
            'amount' => 27516
        ),
        array(
            'level' => 90,
            'amount' => 28235
        ),
        array(
            'level' => 91,
            'amount' => 28965
        ),
        array(
            'level' => 92,
            'amount' => 29706
        ),
        array(
            'level' => 93,
            'amount' => 30458
        ),
        array(
            'level' => 94,
            'amount' => 31221
        ),
        array(
            'level' => 95,
            'amount' => 31995
        ),
        array(
            'level' => 96,
            'amount' => 32780
        ),
        array(
            'level' => 97,
            'amount' => 33576
        ),
        array(
            'level' => 98,
            'amount' => 34383
        ),
        array(
            'level' => 99,
            'amount' => 35201
        ),
        array(
            'level' => 100,
            'amount' => 36030
        ),
        array(
            'level' => 101,
            'amount' => 37030
        ),
        array(
            'level' => 102,
            'amount' => 38330
        ),
        array(
            'level' => 103,
            'amount' => 39930
        ),
        array(
            'level' => 104,
            'amount' => 41830
        ),
        array(
            'level' => 105,
            'amount' => 44030
        ),
        array(
            'level' => 106,
            'amount' => 46530
        ),
        array(
            'level' => 107,
            'amount' => 49330
        ),
        array(
            'level' => 108,
            'amount' => 52430
        ),
        array(
            'level' => 109,
            'amount' => 55830
        ),
        array(
            'level' => 110,
            'amount' => 59530
        ),
        array(
            'level' => 111,
            'amount' => 63530
        ),
        array(
            'level' => 112,
            'amount' => 67830
        ),
        array(
            'level' => 113,
            'amount' => 72430
        ),
        array(
            'level' => 114,
            'amount' => 77330
        ),
        array(
            'level' => 115,
            'amount' => 82530
        ),
        array(
            'level' => 116,
            'amount' => 88030
        ),
        array(
            'level' => 117,
            'amount' => 93830
        ),
        array(
            'level' => 118,
            'amount' => 99930
        ),
        array(
            'level' => 119,
            'amount' => 106330
        ),
        array(
            'level' => 120,
            'amount' => 106330
        )
    );

    public function __construct()
    {
        parent::__construct();
    }

    public function getAttrByUid($uid, $field = '')
    {
		LogApi::logProcess("### getAttrByUid"); //zzzzz
		
        $key = 'user_attribute:' . $uid;
        $query = "select * from user_attribute where uid = $uid";
        $rows = $this->read($key, $query, 0, 'dbMain', false);
		
		
        if (count($rows) == 1) {
			
			LogApi::logProcess("### getAttrByUid 1");
            $data = $rows[0];
			LogApi::logProcess("### getAttrByUid 2");
			
        } else {
			
            $insert = "INSERT INTO `user_attribute` (`uid`) VALUES ($uid)";
            $this->getDbMain()->query($insert, false);
			
            $data = array(
                'uid' => $uid,
                'charm' => '0',
                'heart' => '0',
                'experience' => '0',
                'experience_level' => '0',
                'coin_balance' => '0',
                'point_balance' => '0',
                'default_image' => '0',
                'fb_url' => '',
                'vip' => 0,
                'vip_expiration' => 0,
                'gift_consume' => 0,
                'diamond' => 0,
                'auth' => 0,
                'game_point' => 0,
                'active_point' => 0,
                'channel_point' => 0
            );
	    }
		
        if (!empty($field) && isset($data[$field])) {
            return $data[$field];
        } else {
            return $data;
        }
    }

    public function getUserInfo($uid, $ttl = 300)
    {
        $key = 'user_info:' . $uid;
        $value = $this->getRedisSlave()->get($key);
        if ($value !== false) {
            return json_decode($value, true);
        } else {
            $value = $this->getRedisMaster()->get($key);
            if ($value !== false) {
                return json_decode($value, true);
            } else {
                $data = array();
                $userInfoModel = new UserInfoModel();
            	$userAttr = $this->getAttrByUid($uid);
                $data['uid'] = $uid;
                $data['nick'] = $userInfoModel->getNickName($uid);
                $vipInfo = $this->getVipInfo($userAttr);
                $data['vip'] = $vipInfo['vip'];
                $richManInfo = $this->getRichManLevel($uid, $userAttr['gift_consume'], $userAttr['consume_level']);
                $data += $richManInfo;
                $singerInfo = $this->getExperienceLevel($userAttr['experience']);
                $data += $singerInfo;
                $data['vipLevel'] = $userInfoModel->getVipLevel($uid);
                $this->getRedisMaster()->setex($key, $ttl, json_encode($data));
                return $data;
            }
        }
    }

    public function getVipInfo($userAttr)
    {
        $vip = 0;
        if (time() < $userAttr['vip_expiration']) {
            // 如果vip沒有過期
            $vip = $userAttr['vip'];
        }
        return $this->vipList[$vip];
    }

    public function openVip($uid, $vip)
    {
        $userAttr = $this->getAttrByUid($uid);
        $vipInfo = $this->getVipInfo($userAttr);
        if ($vipInfo['vip'] == $vip && $userAttr['vip_expiration'] > time()) {
            $vipExpiration = $userAttr['vip_expiration'] + 30 * 24 * 3600;
        } else {
            $vipExpiration = time() + 30 * 24 * 3600;
        }
        $query = "update user_attribute set vip = $vip , vip_expiration = $vipExpiration where uid = $uid ";
        $rs = $this->getDbMain()->query($query);
        if ($rs) {
            $this->cleanCache($uid);
        }
        return array(
            'vip' => $vip,
            'vipExpiration' => date('Y-m-d H:i:s', $vipExpiration)
        );
    }

    public function getFlower($uid)
    {
        $ttl = 3600;
        $key = 'user_flower:' . $uid;
        $query = "select flower from receive_flower where uid = $uid ";
        $rows = $this->read($key, $query, $ttl, 'dbFlower');
        if ($rows && count($rows) == 1) {
            return $rows[0]['flower'];
        } else {
            return 0;
        }
    }

    public function getSessOwner($sid)
    {
	    $ttl = 3600;
	    $key = 'sess_owner:' . $sid;
	    $query = "select * from smember where sid=$sid and type=255 ";
		LogApi::logProcess("getSessOwner------------------" . $query);
	    $rows = $this->read($key, $query, $ttl, 'dbRaidcall');
		LogApi::logProcess("getSessOwner---------------read" . $query);
	    if ($rows && count($rows) == 1) {
		    return $rows[0]['uid'];
	    } else {
		    return 0;
	    }
    }

    public function getStatusByUid($uid, $field)
    {
        $key = 'user_status:' . $uid;
        return $this->getRedisMaster()->hGet($key, $field);
    }

    public function delStatusByUid($uid, $field)
    {
        $key = 'user_status:' . $uid;
        return $this->getRedisMaster()->hDel($key, $field);
    }

    public function setStatusByUid($uid, $field, $value)
    {
        $key = 'user_status:' . $uid;
        $this->getRedisMaster()->hSet($key, $field, $value);
    }

    public function setGuardEndTime($uid, $singerUid, $endTime)
    {
	$key = 'guard_time:' . $uid;
	$this->getRedisMaster()->hSet($key, $singerUid, $endTime);	
    }

    public function getGuardEndTime($uid, $singerUid)
    {
        $key = 'guard_time:' . $uid;
        return $this->getRedisMaster()->hGet($key, $singerUid);
    }

    public function statusIncrease($uid, $field, $value = 1)
    {
        $key = 'user_status:' . $uid;
        $value = intval($value);
        return $this->getRedisMaster()->hIncrBy($key, $field, $value);
    }

    public function addHeartByUid($uid, $qty)
    {
        $query = "update user_attribute set heart = heart + $qty where uid = $uid ";
        $rs = $this->getDbMain()->query($query);
        if ($rs) {
            // $this->statusIncrease($uid, 'num_heart_convert', $qty);
            $this->cleanCache($uid);
        }
        return $rs;
    }

    public function addExperienceByUid($uid, $qty)
    {
        $query = "update user_attribute set experience = experience + $qty where uid = $uid ";
        $rs = $this->getDbMain()->query($query);
        if ($rs) {
            $this->cleanCache($uid);
        }
        return $rs;
    }

    public function cleanCache($uid)
    {
        $key = 'user_attribute:' . $uid;
        $this->clean($key);
    }

    public function getActivity($userAttr)
    {
        $activityModel = new ActivityModel();
        if($activityModel->isActivityOpen()){
            return empty($userAttr['game_point'])?0:floor($userAttr['game_point']/50);
        }else{
            return false;
        }
    }

    public function getSingerTitle($level = 1)
    {
        $data = array();
        if ($level >= 60) {
            $data[0] = 6;
            $data[1] = '秀场天后';
        } elseif ($level >= 50) {
            $data[0] = 5;
            $data[1] = '秀场巨星';
        } elseif ($level >= 40) {
            $data[0] = 4;
            $data[1] = '秀场明星';
        } elseif ($level >= 30) {
            $data[0] = 3;
            $data[1] = '秀场偶像';
        } elseif ($level >= 20) {
            $data[0] = 2;
            $data[1] = '秀场新星';
        } elseif ($level >= 10) {
            $data[0] = 1;
            $data[1] = '秀场新秀';
        } else {
            $data[0] = 0;
            $data[1] = '秀场新人';
        }
        return $data;
    }

    public function getExperienceChange($cur_lvl, $new_lvl)
    {
        $currentExpeInfo = $this->getExperienceLevel($cur_lvl);
        $newExpeInfo = $this->getExperienceLevel($new_lvl);
        if ($newExpeInfo['singerLevel'] > $currentExpeInfo['singerLevel']) {
            return $newExpeInfo;
        } else {
            return false;
        }
    }
    
    public function getExperienceLevelFromDB($lvl)
    {        
        $query = "select t.level, t.level_name, t.exp, t.charm, p.parm3 as display_id from cms_manager.anchor_level t 
            left join card.parameters_info p on p.id = 19 
            where t.level = $lvl";
        $rows = $this->getDbMain()->query($query);
        $data = array();
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $data['level'] = (int)$row['level'];
            $data['exp'] = (int)$row['exp'];
            $data['level_name'] = (int)$row['level_name'];
            $data['display_id'] = (int)$row['display_id'];
            $data['charm'] = (int)$row['charm'];
        }else{
            LogApi::logProcess("UserAttributeModel:getExperienceLevelFromDB return null, sql:$query");
        }
                        
        return $data;
        
    }

    public function getExperienceLevel($lvl)
    {
        $result = array();
        $result['experience'] = 0; // 經驗
        
        $data = $this->getExperienceLevelFromDB($lvl);
        if(!empty($data)){
            $result['singerLevel'] = $data['level']; // 等級
            $result['currentLevelExperience'] = $data['exp']; // 當前等級的經驗
            $result['singerTitle'] = $data['level_name'];
            $result['display_id'] = $data['display_id'];
            $result['charm'] = $data['charm'];
        }else{
            LogApi::logProcess("UserAttributeModel:getExperienceLevel result is null. lvl:$lvl");
        }

        return $result;
    }
    
    //送礼增加活跃度
    public function addActivePointBySendGift($uid)
    {
        $key = "gift_times";
	    $num = $this->getRedisMaster()->zIncrBy($key,1, $uid);
	    if(3 < $num){
	        return;
	    }
	    
	    $this->addActivePoint($uid, 3);
    }
    
    //发言增加活跃度
    public function sayCurDayTimes($uid)
    {
        $key = "say_times";
	    $num = $this->getRedisMaster()->zIncrBy($key,1, $uid);
	    if(5 < $num){
	        return;
	    }
	    
	    $this->addActivePoint($uid, 1);
    }
    
    public function getActiveLevelFromDB($lvl)
    {
        $query = "select $lvl as active_level, 
        sum(t.life) as life, sum(t.attack) as attack, sum(t.dodge) as dodge, sum(t.critical) as critical, sum(t.avoid) as avoid, sum(t.speed) as speed, 
        p.parm3 as display_id from cms_manager.user_active t 
            left join card.parameters_info p on p.id = 21 where t.active_level<=$lvl";
        $rows = $this->getDbMain()->query($query);
        
        $level = array();
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $level['active_level'] = (int)$row['active_level'];
            $level['display_id'] = (int)$row['display_id'];
            $level['life'] = $row['life'];
            $level['attack'] = $row['attack'];
            $level['dodge'] = $row['dodge'];
            $level['critical'] = $row['critical'];
            $level['avoid'] = $row['avoid'];
            $level['speed'] = $row['avoid'];
            return $level;
        } 
        
        LogApi::logProcess("getActiveLevelFromDB:: return null, sql:$query");
        
        return $level;
        
    }
    
    //根据分值获得用户活跃等级
    public function getActiveLevel($score, $uid, $lvl)
    {        
        if (empty($lvl)) {
            $activeManInfo = array();
            $uattr = $this->getAttrByUid($uid);
            $lvl = $uattr['active_level'];
        }

        $level = $this->getActiveLevelFromDB($lvl);
        $flag = 0;
        $query = "select * from card.parameters_info t where t.id = 43";
        $rows = $this->getDbMain()->query($query);
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $leveltmp = (int)$row['parm1'];
            if($level['active_level'] >= $leveltmp)
            {
                $flag = 1;
            }
        }
        
        $activeManInfo['activeManLevel'] = $level['active_level'];
        $activeManInfo['activeManTitle'] = '';
        $activeManInfo['activeManEffect'] = $flag;
        $activeManInfo['display_id'] = $level['display_id'];
        $activeManInfo['life'] = $level['life'];
        $activeManInfo['attack'] = $level['attack'];
        $activeManInfo['dodge'] = $level['dodge'];
        $activeManInfo['critical'] = $level['critical'];
        $activeManInfo['avoid'] = $level['avoid'];
        $activeManInfo['speed'] = $level['avoid'];
        
        LogApi::logProcess("getActiveLevel:: return data:".json_encode($activeManInfo));
        
        return $activeManInfo;
    }
    
    //获得升级阶段的最靠后宝箱id
    public function getUpLevelBoxid($oldLevel, $newLevel)
    {
        $data = array();
        
        $sql = "select t.box_id, t.is_all from cms_manager.user_money t 
            where t.money_level > $oldLevel and t.money_level < $newLevel 
            and t.box_id != 0 order by t.money_level desc";
        $rows = $this->getDbMain()->query($sql);
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $data['box_id'] = (int)$row['box_id'];
            $data['is_all'] = (int)$row['is_all'];
        }
        /* for ($i = count($this->giftConsumeList) - 1; $i >= 0; $i--) {
            $level = $this->giftConsumeList[$i]['level'];
            $boxid = $this->giftConsumeList[$i]['boxid'];
            if ($level > $oldLevel && $level < $newLevel && 0 != $boxid) {
                $data['boxid'] = $boxid;
                $data['is_all'] = $this->giftConsumeList[$i]['is_all'];
                break;
            }
        } */
        
        return $data;
    }
    
    public function getRichManLevelFromDB($lvl)
    {      
        $row = $this->get_user_consume_conf($lvl);
        
        $data = array();
        if (!empty($row)) {
            $data['level'] = (int)$row['money_level'];
            $data['title'] = $row['title'];
            $data['child_level'] = (int)$row['child_level'];
            $data['level_value'] = (int)$row['total_gold'];
            $data['box_id'] = (int)$row['box_id'];
            $data['is_all'] = (int)$row['is_all'];
            $data['glory'] = $row['glory'];
            
            $query = "select sum(life) as life,sum(attack) as attack, sum(dodge) as dodge, sum(critical) as critical, sum(avoid) as avoid, sum(speed) as speed,
            max(skill_level) as skill_level from cms_manager.user_money where money_level<=" . $row['money_level'];
            
            $rows = $this->getDbMain()->query($query);
            if ($rows && $rows->num_rows > 0) {
            	$row = $rows->fetch_assoc();
            	$data['life'] = $row['life'];
            	$data['attack'] = $row['attack'];
            	$data['dodge'] = $row['dodge'];
            	$data['critical'] = $row['critical'];
            	$data['avoid'] = $row['avoid'];
            	$data['speed'] = $row['speed'];
            	$data['skill_level'] = $row['skill_level'];
            }
            
            return $data;
        }
                
        return $data;
    }
	
	public function isSinger($uid){ 
		$key = 'uid:' . $uid;
		$value = $this->getRedisMaster()->get($key);
        if ($value !== false) {
            $user = json_decode($value, true);
            if($user['identity'] == 2){
            	return true;
            }
        }else{
        	//检查是否创建有房间/是否是主播
			$query = "select * from raidcall.anchor_info where flag = 1 and uid = $uid";
			$rs = $this->getDbMain()->query($query);
			
			if ($rs && $rs->num_rows > 0) {
		        return true;
		    }
        }
        
		return false;
	}

    public function getRichManLevel($uid, $giftConsume, $lvl)
    {
		LogApi::logProcess("getRichManLevel");
		
        if (empty($lvl)) {
            $uattr = $this->getAttrByUid($uid);
            $lvl = $uattr['consume_level'];
        }

		$data = $this->getRichManLevelFromDB($lvl);
		
		$now = time();
        $richManInfo = array();
        $richManInfo['richManValue'] = $giftConsume;
        $richManInfo['richManStart'] = 0;
        $richManInfo['richManLevel'] = $data['level'];
        $richManInfo['richManTitle'] = $data['title'];
        $richManInfo['child_level'] = $data['child_level'];
        $richManInfo['currentRichLevelValue'] = $data['level_value'];
        $richManInfo['boxid'] = $data['box_id'];
        $richManInfo['is_all'] = $data['is_all'];
        
        $richManInfo['life'] = $data['life'];
        $richManInfo['attack'] = $data['attack'];
        $richManInfo['dodge'] = $data['dodge'];
        $richManInfo['critical'] = $data['critical'];
        $richManInfo['avoid'] = $data['avoid'];
        $richManInfo['speed'] = $data['speed'];
        $richManInfo['skill_level'] = $data['skill_level'];
        $richManInfo['glory'] = $data['glory'];
        
        $level = $richManInfo['richManLevel'];
        $isEffect = 0;
        $query = "select * from card.parameters_info t where t.id = 42";
        $rows = $this->getDbMain()->query($query);
        if ($rows && $rows->num_rows > 0) {
            $row = $rows->fetch_assoc();
            $leveltmp = (int)$row['parm1'];
            if($level >= $leveltmp)
            {
                $isEffect = 1;
            }
        }
        $flag = $this->isSinger($uid);
        if($flag){
            $isEffect = 0;
        }
        $richManInfo['richManEffect'] = $isEffect;
        
        return $richManInfo;
    }
    public function getResponseInfo($userAttr, $isSinger = false) //zzzzz
    {
	//$userAttr = $this->getAttrByUid($userAttr['uid']);//add by lixu
		$uid = $userAttr['uid'];
        foreach (array(
                     'uid' => 'uid',
                     'charm' => 'charm'
                 ) as $key => $val) {
            $result[$val] = $userAttr[$key];
        }
				
        if ($isSinger) {
            $toolSubsModel = new ToolSubscriptionModel();
            $result['background'] = $this->getStatusByUid($uid, 'background');
            if (!$toolSubsModel->hasTool($uid, $result['background'])) {
                $this->setStatusByUid($uid, 'background', '');
                $result['background'] = '';
            }

            $result['effect'] = $this->getStatusByUid($uid, 'effect');
            $result['receivedCoins'] = $this->getStatusByUid($uid, 'received_coins');
            $result['fansNum'] = $this->getFansNumber($uid);
			
            // 秀場活動，主播的排名信息
            $activityModel = new ActivityModel();
            $result += $activityModel->getSingerRank($userAttr);
        } else {
            $result['coinBalance'] = $userAttr['coin_balance'];//1000
            $result['pointBalance'] = $userAttr['point_balance'];
            $result['heart'] = $userAttr['heart'];
            $heartConvertRecordModel = new HeartConvertRecordModel();
            $result['hour'] = $heartConvertRecordModel->convertInterval($uid);
            $activityModel = new ActivityModel();
            $result['dailyPacketStatus'] = $activityModel->getDailyPacketStatus($uid);
            $result['follows'] = $this->getFollows($uid);
        }
		
        // 用户上麦头像
        $result['images'] = array();
        if (!empty($userAttr['default_image'])) {
            // 表示用戶有頭像
            $settingsModel = new SettingsModel();
            $userImageModel = new UserImageModel();
            $onmicImageUrl = $settingsModel->getValue('ONMIC_IMAGE_URL');
            $images = $userImageModel->getImagesByUid($uid);
            if (count($images) > 0) {
                foreach ($images as $image) {
                    if ($image['size'] == UserImageModel::SIZE_STANDARD) {
                        $result['images'][] = $onmicImageUrl . $image['image_id'] . '&t=' . $image['last_modified'];
                    }
                }
            }
        }
		
        $result['faceBookUrl'] = $userAttr['fb_url'];
        $result['homePageUrl'] = 'http://www.showoo.cc/rcec/index.php?cmd=showPersonalHome';
        $userInfoModel = new UserInfoModel(); //hhhhhhhh
		
		LogApi::logProcess("00000000");
		
		
        $result['silver'] = $userInfoModel->getSilver($uid);
		LogApi::logProcess("11111111");
        $result['flower'] = 0;//$this->getFlower($uid);
		LogApi::logProcess("222222");
        $result += $this->getExperienceLevel($userAttr['experience']);
		LogApi::logProcess("333333333333333");
        $result += $this->getRichManLevel($uid, $userAttr['gift_consume'], $userAttr['consume_level']);

        // 獲取蛋蛋信息
        $eggs = $this->getStatusByUid($uid, 'eggs');
		
        if (empty($eggs)) {
            $luckyDrawModel = new LuckyDrawModel();
            $eggs = $luckyDrawModel->refreshEgg($uid);
        } else {
            $eggs = explode(',', $eggs);
        }
        $result['eggs'] = $eggs;
         //活動，如沒有活動請註釋下面一行
         $result['luckyShakeCount'] = $this->getActivity($userAttr);

        // VIP信息
        $vipInfo = $this->getVipInfo($userAttr);
        $result['vip'] = $userAttr['vip'];
        $result['auth'] = $userAttr['auth'];
        $result['isNew'] = ($userAttr['gift_consume'] > 0) ? 0 : 1;
        // 讀公告的最後時間
        $result['lastTimeReadNotice'] = $this->getStatusByUid($uid, 'last_time_read_notice');
		
        return $result;
    }

    public function deductCoin($uid, $coin)
    {
        $uid = (int)$uid;
        $coin = (int)$coin;
        if ($coin <= 0) {
            return false;
        }
        $query = "update user_attribute set coin_balance = coin_balance - $coin where uid = $uid and coin_balance >= $coin ";
        $rs = $this->getDbMain()->query($query);
        if ($rs == true && $this->getDbMain()->affected_rows > 0) {
            $this->cleanCache($uid);
            return true;
        }
        return false;
    }

    public function addCoin($uid,$coin,$recordParam = array()){
        $uid = (int)$uid;
        $coin = (int)$coin;
        if ($coin <= 0) {
            return false;
        }
        $query = "update user_attribute set coin_balance = coin_balance + $coin where uid = $uid ";
        $rs = $this->getDbMain()->query($query);
        if ($rs == true && $this->getDbMain()->affected_rows > 0) {
            if(!empty($recordParam)){
                $recordParam['uid'] = $uid;
                $recordParam['record_time'] = time();
                $keyString = '(';
                $valueString = '(';
                foreach($recordParam as $key=>$value){
                    $keyString = $keyString . $key . ',';
                    $valueString = $valueString . $value . ',';
                }
                $keyString = substr($keyString,0,-1) . ')';
                $valueString = substr($valueString,0,-1) . ')';
                $query = "insert into show_coin_account_record {$keyString} values {$valueString}";
                $this->pushToMessageQueue('rcec_record',$query);
            }
            $this->cleanCache($uid);
            return true;
        }
        return false;
    }

    public function addPoint($uid, $point)
    {
        $uid = (int)$uid;
        $coin = (int)$point;
        if ($point <= 0) {
            return false;
        }
        $query = "update user_attribute set point_balance = point_balance + $point,channel_point = channel_point+$point where uid = $uid";
        $rs = $this->getDbMain()->query($query);
        if ($rs == true && $this->getDbMain()->affected_rows > 0) {
            $this->cleanCache($uid);
            return true;
        }
        return false;
    }

    public function updateMonthPoint($userPoint, $singerUid)
    {
        // 累積計算月度秀點
        if ($userPoint > 0) {
            $month = date('Ym', time() - 32400);
            $this->getRedisMaster()->zIncrBy('receiver_points_' . $month, $userPoint, $singerUid);
        }
    }

    public function follow($uid, $followUid)
    {
        $now = time();
        $sql = "INSERT INTO cms_manager.follow_user_record(uid, fid, create_time, type)" .
            " values($uid, $followUid, $now, 1)";
        $result = $this->getDbMain()->query($sql);
        
        file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "用户（$uid）关注用户（$followUid）, sql:$sql\n", FILE_APPEND);
        $this->getRedisMaster()->sAdd('i_follow:' . $uid, $followUid);
        $this->getRedisMaster()->sAdd('follow_me:' . $followUid, $uid);
        $this->updateFansRank($followUid); 
    }

    public function getFollowNumber($uid)
    {
        return $this->getRedisMaster()->sSize('i_follow:' . $uid);
    }

    public function getFollows($uid)
    {
        return $this->getRedisMaster()->sMembers('i_follow:' . $uid);
    }

    public function isFollow($uid, $singerUid)
    {
        return $this->getRedisMaster()->sIsMember('i_follow:' . $uid, $singerUid);
    }

    public function unfollow($uid, $singerUid)
    {
        $sql = "DELETE FROM cms_manager.follow_user_record WHERE uid = $uid AND fid = $singerUid";
        $result = $this->getDbMain()->query($sql);
        
        file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "unfollow --uid=$uid,singerUid=$singerUid\n", FILE_APPEND);
        $this->getRedisMaster()->sRemove('i_follow:' . $uid, $singerUid);
        $this->getRedisMaster()->sRemove('follow_me:' . $singerUid, $uid);
        $this->updateFansRank($singerUid);
    }

    public function getFans($uid)
    {
        return $this->getRedisMaster()->sMembers('follow_me:' . $uid);
    }

    public function getFansNumber($uid)
    {
        /* $sql = "select count(*) from cms_manager.follow_user_record where fid=$uid";
        $result = mysql_query($sql);
        $size=mysql_num_rows($result);
         
        if ($size > 0) {
            $row=mysql_fetch_row($result);
            $num = $row[0];
        }
        
        return $num; */
        return $this->getRedisMaster()->sSize('follow_me:' . $uid);
    }

    public function canCallFans($uid)
    {
        $lastTime = $this->getStatusByUid($uid, 'last_time_call_fans');
        if (date('ymd') == date('ymd', $lastTime)) {
            return false;
        } else {
            return true;
        }
    }
    public function addShowBiHistory($uid,$time,$amount,$type)
    {
        $query = "INSERT INTO show_bi_his (uid, create_time,amonut, type,status) VALUES ($uid, $time,$amount, $type,0) ";
        file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "UserAttributeModel::addShowBiHistory sql:$query\n", FILE_APPEND);
		$this->pushToMessageQueue('rcec_record', $query);
    }
    public function addShowBiRecord($uid, $price, $orderId, $productName, $duration){
    	$query = "INSERT INTO show_bi_his (uid,amount,order_id,type,status,create_time,product_name,product_num) VALUES ($uid, $price, $orderId, 4, 0, now(), '$productName', $duration)";
        file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "UserAttributeModel::addShowBiRecord sql:$query\n", FILE_APPEND);
		$this->pushToMessageQueue('rcec_record', $query);
    }
    public function updateFansRank($singerUid)
    {
	
       	file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "updatefansrank $singerUid\n", FILE_APPEND);
        $userAttr = $this->getAttrByUid($singerUid);
	if( empty($userAttr))
	{
        	file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "empty\n", FILE_APPEND);
	}
	else
	{
		
       		file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", " $userAttr\n", FILE_APPEND);
	}
        if (!empty($userAttr) and $userAttr['auth'] == 1) {
            $num = $this->getRedisMaster()->sCard('follow_me:' . $singerUid);

            $this->getRedisMaster()->zAdd('rank_singer_fans', $num, $singerUid);
        }
    }

    public function logActiveUser($uid, $version = '')
    {
        $lastTime = $this->getStatusByUid($uid, 'last_time_init_show');
        if (date('ymd') != date('ymd', $lastTime)) {
            $now = time();
            $this->setStatusByUid($uid, 'last_time_init_show', $now);
            $query = "INSERT INTO `active_user_record` (`record_time`, `uid`, `version`) VALUES ($now, $uid, '$version') ";
            $this->pushToMessageQueue('rcec_record', $query);
        }
    }
    
    //更新用户活跃数值
    public function addActivePoint($uid, $point)
    {
        $tmpFileStr = "/tmp/activelock.txt";
        $lockhandle = PhpLock::lock_thisfile($tmpFileStr,true);
        
        if($lockhandle){
            $userAttr = $this->getAttrByUid($uid);
            $activeManInfo = $this->getActiveLevel($userAttr['active_point']+$point, $uid, 0);
            $active_level = $activeManInfo['activeManLevel'];
            
            $query = "update user_attribute set active_point = active_point + $point,
            active_level = $active_level where uid = $uid";
            $rs = $this->getDbMain()->query($query);
            if(!$rs){
                LogApi::logProcess("addActivePoint :: exe sql error, sql:$query");
            }
            $redisKey = "user_attribute:{$uid}";
            // 更新redis缓存
            $this->getRedisMaster()->del($redisKey);
            
            // DBLE
            $db_main = $this->getDbMain();
            $date = date("Y-m-d");
            $sql = "SELECT uid FROM rcec_record.user_active_record WHERE uid=$uid AND createtime='$date'";
            $rows = $db_main->query($sql);
            if (!empty($rows) && $rows->num_rows > 0) {
                $sql = "UPDATE rcec_record.user_active_record SET active_point=active_point+$point WHERE uid=$uid AND createtime='$date'";
            } else {
                $sql = "INSERT INTO rcec_record.user_active_record(uid, active_point, createtime) VALUES ($uid, $point, '$date')";
            }

            $rows = $db_main->query($sql);
            if (empty($rows) || $db_main->affected_rows <= 0) {
                LogApi::logProcess("[DBLElog] addActivePoint :: exe sql error, sql:$sql");
                return false;
            }
            
            PhpLock::unlock_thisfile($lockhandle);
            LogApi::logProcess('****************addActivePoint 正常执行.');//:' . json_encode($result));
        }else{
            LogApi::logProcess('****************addActivePoint 锁失败');
        }
    }

    public function addGamePoint($uid, $point, $game, $type, $coin = 0)
    {
        $uid = (int)$uid;
        $point = (int)$point;
        if (empty($coin)) {
            $query = "update user_attribute set game_point = game_point + $point where uid = $uid";
        } else {
            $query = "update user_attribute set game_point = game_point + $point,coin_balance = coin_balance - $coin
                where uid = $uid and coin_balance >= $coin";
        }
        $rs = $this->getDbMain()->query($query);
        if ($rs == true && $this->getDbMain()->affected_rows > 0) {
            $now = time();
            $logQuery = "insert into game_point_record (uid,record_time,type,game,num) values ($uid,$now,$type,$game,$point)";
            $this->pushToMessageQueue('rcec_record', $logQuery);
            $this->cleanCache($uid);
            return true;
        }
        return false;
    }

    public function deductGamePoint($uid, $point, $type, $game)
    {
        $uid = (int)$uid;
        $point = (int)$point;
        if ($point <= 0) {
            return false;
        }
        $query = "update user_attribute set game_point = game_point - $point where uid = $uid and game_point >= $point ";
        $rs = $this->getDbMain()->query($query);
        if ($rs == true && $this->getDbMain()->affected_rows > 0) {
            $now = time();
            $cost = -$point;
            $logQuery = "insert into game_point_record (uid,record_time,type,game,num) values ($uid,$now,$type,$game,$cost)";
            $this->pushToMessageQueue('rcec_record', $logQuery);
            $this->cleanCache($uid);
            return true;
        }
        return false;
    }

    public function getCarInfoByUid ($uid, $field = '')
    {
        $now = time();
        $key = 'carinfo_carid:' . $uid;
        $query = "select c.* from car_buy cb, car c where cb.uid=$uid and cb.type=1 and cb.end_time > $now and cb.cid=c.id";
        //file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "$query $key\n", FILE_APPEND);
        $rows = $this->read($key, $query, 10, 'dbMain', false);
        if (count($rows) == 1) {
            $data = $rows[0];
             if (!empty($field) && isset($data[$field])) {
                return $data[$field];
            } else {
                return $data;
            }
        } 
        return null;
    }
     public function getCarNumberByUid ($uid, $field = '')
    {
        $now = time();
        $key = 'carinfo_carnumber:' . $uid;
        $query = "select * from car_number where uid=$uid and status=1 and end_time >$now";
       // file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "$query $key\n", FILE_APPEND);
        $rows = $this->read($key, $query, 10, 'dbMain', false);
        if (count($rows) == 1) {
            $data = $rows[0];
             if (!empty($field) && isset($data[$field])) {
                return $data[$field];
            } else {
                return $data;
            }
        } 
        return null;
    }

     public function delChannelCarinfo($sid,$uid)
      {
        $key = "ChannelCarInfo:".$sid;
        $value = $this->getRedisMaster()->get($key);
        if ($value !== false) {
             $carinfo = json_decode($value,true);
             unset($carinfo[array_search($uid,$carinfo)]);
             $this->getRedisMaster()->set($key, json_encode($carinfo));
        }
      }
      public function addChannelCarinfo($sid,$uid,$carid,$carnum)
      {      
        $key = "ChannelCarInfo:".$sid;
        $value = $this->getRedisMaster()->get($key);  
        if ($value !== false) {
             $carinfo = json_decode($value,true);
             $data = array();
             $data['uid'] =$uid;
             $data['carid'] = $carid;
             $data['carnum'] = $carnum;
             $carinfo[$uid] = $data;
          
             $this->getRedisMaster()->set($key, json_encode($carinfo));
        }
        else
        {
             $data = array();
             $data['uid'] =$uid;
             $data['carid'] = $carid;
             $data['carnum'] = $carnum;
             $carinfo[$uid] = $data;
             $this->getRedisMaster()->set($key, json_encode($carinfo));
        }
      }
      public function getChannelCarinfobyCid($sid)
      {
         
        $key = "ChannelCarInfo:".$sid;
        $value = $this->getRedisMaster()->get($key);
        if ($value !== false) {
            $ret=  json_decode($value, true);      
        }
        else
        {
            $ret = array();
        }
        return $ret;
      }
	  
	  //添加直播间用户等级信息到redis
      public function addChannelUserLevelinfoToRedis($sid,$uid)
      {      

        return;
        // 不明觉厉           
        $now = time();
        $userAttr = $this->getAttrByUid($uid);
        $explevelinfo = $this->getExperienceLevel($userAttr['experience']);
        $richlevelinfo = $this->getRichManLevel($uid, $userAttr['gift_consume'], $userAttr['consume_level']);
        $money = $userAttr['gift_consume'];
        $level = $richlevelinfo['richManLevel'] ;
        $key = "ChannelUserLevelInfo:".$sid;
        $value = $this->getRedisMaster()->get($key);  
        if ($value !== false) {
             $info = json_decode($value,true);
             $data = array();
             $data['uid'] =$uid;
             $data['explvl'] = $explevelinfo['singerLevel'];
             $data['richlvl'] = $richlevelinfo['richManLevel'];
             $data['lasttime'] = $now;
             $info[$uid] = $data;
             $this->getRedisMaster()->set($key, json_encode($info));
        }
        else
        {
             $data = array();
             $data['uid'] =$uid;
             $data['explvl'] = $explevelinfo['singerLevel'];
             $data['richlvl'] = $richlevelinfo['richManLevel'];
             $data['lasttime'] = $now;
             $info[$uid] = $data;
             $this->getRedisMaster()->set($key, json_encode($info));
        }
      }

      public function addShowHeart($uid,$num)
      {      
        $keyself = "ShowHeartSelf:";
        $this->getRedisMaster()->zIncrBy($keyself,$num,$uid);
        $key = "ShowHeart:";
        $this->getRedisMaster()->zIncrBy($key,$num,$uid);
      }
      public function getShowHeart($uid)
      {
        $key = "ShowHeartSelf:";
        return  $this->getRedisMaster()->zScore($key,$uid);
        
      }
      public function setShowHeartInfo($uid,$str)
      {
        $key = "ShowHeartInfo:";
        $this->getRedisMaster()->hSet($key,$uid,$str);

        $keyself = "ShowHeartSelf:";
        $this->getRedisMaster()->zRem($keyself,$uid);
      }
      public function getShowHeartInfo($uid)
      {
          $key = "ShowHeartInfo:";
          return $this->getRedisMaster()->hGet($key,$uid);
      }
      // 重置主播本场次主播收入
      public function resetSingerChannelPoint($singerUid){
        $query = "update user_attribute set channel_point = 0 where uid = $singerUid";
        $rs = $this->getDbMain()->query($query);
        if ($rs == true && $this->getDbMain()->affected_rows > 0) {
            $this->cleanCache($singerUid);
            return true;
        }
        return false;
      }
      //kay :添加用户id到房间。
      public function addCacheUserIdList($room_id,$singerUid,$uid,$activeLevel,$isguard){
          if($singerUid == $uid){
              return ;
          }
             $allUserKey = "roomalluser_".$room_id;
             //$key = "roomuserlist_".$room_id;
             $key = member_list::HashUserListInfoKey($room_id);
             // $arr = $this->getRedisMaster()->lrange($key,0,$this->getRedisMaster()->llen($key));           
             
             $data = array(
                 'uid' => $uid,
                 'activeLevel' => $activeLevel,
                 'isguard' => $isguard //0：否 1：是
             );
             
             $jsondata = json_encode($data);
             $this->getRedisMaster()->zSet($key,$uid,$jsondata);
             // if(!in_array($jsondata,$arr)){
             //         $this->getRedisMaster()->lpush($key,$jsondata);
             // } 
             
             $this->getRedisMaster()->sAdd($allUserKey, $uid);
//              if(){
//                  //zkay统计人数
//                  $peakValue = $this->UpCachePeakValue($room_id,1);
//              }
      }
      
      //获得直播间在线累计观看人数
      public function getPeakValue($sid){
          $allUserKey = "roomalluser_".$sid;
          $peakNum = $this->getRedisMaster()->sSize($allUserKey);
//         $scoreKey = 'roompeakvalue:score';
//         $peakNum = getRedisMaster()->ZSCORE($scoreKey,$sid);
        return $peakNum;
      }
      
      //获得直播间在线实际观看人数
      public function getOnlineUserCount($sid){
        $key = member_list::HashUserListInfoKey($sid);
        $num = $this->getRedisMaster()->hLen($key);
          // $scoreKey = "roomuserlist_".$sid;
          // $num = $this->getRedisMaster()->LLEN($scoreKey);
//         $scoreKey = 'roomusercount:score';
//         $num = getRedisMaster()->ZSCORE($scoreKey,$sid);
        return $num;
      }
      
//       public  function UpCachePeakValue($sid,$score){
//           $scoreKey = 'roompeakvalue:score';
//           $result ="";
//           if($sid!=""){
//               $result = getRedisMaster()->zIncrBy($scoreKey,$score,$sid);
//           }
//           file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "**********UpCachePeakValue:: result($result) "." $sid \n", FILE_APPEND);
          
//           return $result;
          
//       }
//       public function UpCacheUserCount($sid,$score){
//            $scoreKey = 'roomusercount:score';
//            $result = getRedisMaster()->zIncrBy($scoreKey,$score,$sid);
           
//            file_put_contents("/data/vnc_log/vnc/vnc_fpm_script/1.log", "***********UpCacheUserCount:: result($result) "." $sid \n", FILE_APPEND);
            
//            return $result;
           
//       }
      //kay :删除用户id到房间。
      public function delCacheUserIdList($sid, $uid){
          LogApi::logProcess('begin delCacheUserIdList::直播间'.$sid.' 用户id：'.$uid);
           // $key = "roomuserlist_".$sid;
           $key = member_list::HashUserListInfoKey($sid);
           $this->getRedisMaster()->hDel($key,$uid);
           LogApi::logProcess('delCacheUserIdList::从直播间'.$sid.'删除用户'.$uid);
           /* $data = array(
               'uid' => $uid,
               'activeLevel' => $activeLevel,
               'isguard' => $isguard //0：否 1：是
           );
            
           $jsondata = json_encode($data); */
           
           // $listvalues = $this->getRedisMaster()->lRange($key, 0, -1);
           // LogApi::logProcess('delCacheUserIdList::直播间'.$sid.'用户列表：'.json_encode($listvalues));
           // foreach ($listvalues as $value){
           //     LogApi::logProcess('遍历用户列表值：'.$value);
           //     if(strpos($value, $uid)){
           //         $this->getRedisMaster()->lrem($key,$value,0);
           //         LogApi::logProcess('delCacheUserIdList::从直播间'.$sid.'删除用户'.$uid);
           //         break;
           //     }
           
           // }
           
           // $listvalues = $this->getRedisMaster()->lRange($key, 0, -1);
           // LogApi::logProcess('delCacheUserIdList::直播间'.$sid.'更新后用户列表：'.json_encode($listvalues));
           
           /* if(getRedisMaster()->lrem($key,$jsondata,0)){
               LogApi::logProcess("删除成功delCacheUserIdList::*******sid:$sid*********uid::$uid");
               return true;
           }else{
               LogApi::logProcess("删除失败delCacheUserIdList::*******sid:$sid*********uid::$uid");
               return false;
           } */
      }
      
	public function getAnchorLevelName($level)
	{
		$sql = "select * from cms_manager.anchorLevelNameConfig where levelEnd >= $level and levelStart <= $level";
		$rows = $this->getDbMain()->query($sql);
		
		if (!empty($rows) && $rows->num_rows > 0) {
			$row = $rows->fetch_assoc();
			return $row;
		}
		
		return false;
	}
	
	public function getTaillight($uid)
	{
		$key = "xcbbr_web:HotRankListManage:getUserConsumeRank:";
		return $this->getRedisMaster()->hGet($key, $uid);
	}
		
	// read user_consume_exp_conf from redis or mysql.
	public function get_user_consume_conf($level)
	{
		$key = "h_user_consume_exp_conf";
		$field = $level . "";
		
		$redis = $this->getRedisMaster();
		$ret = $redis->hGet($key, $field);
		
		if (!empty($ret)) {
			return json_decode($ret, true);
		}
		
		$sql = "SELECT * FROM cms_manager.user_money WHERE money_level=$level";
		$db_cms = $this->getDbMain();
		$rows = $db_cms->query($sql);
		
		if (!empty($rows) && $rows->num_rows > 0) {
			$row = $rows->fetch_assoc();
			$redis->hSet($key, $field, json_encode($row));
			return $row;
		}
		
		return null;
	}
	// read user_active_exp_conf from redis or mysql.
	public function get_user_active_conf($level)
	{
		$key = "h_user_active_exp_conf";
		$field = $level . "";
		$redis = $this->getRedisMaster();
		$ret = $redis->hGet($key, $field);
		
		if (!empty($ret)) {
			return json_decode($ret, true);
		}
		
		$sql = "SELECT * FROM cms_manager.user_active WHERE active_level=$level";
		$db_cms = $this->getDbMain();
		$rows = $db_cms->query($sql);
		if (!empty($rows) && $rows->num_rows > 0) {
			$row = $rows->fetch_assoc();
			$redis->hSet($key, $field, json_encode($row));
			return $row;
		}
		
		return null;
	}
	// read anchor_exp_conf from redis or mysql.
	public function get_anchor_exp_conf($level)
	{
		$key = "h_anchor_exp_conf";
		$field = $level . "";
		
		$redis = $this->getRedisMaster();
		$ret = $redis->hGet($key, $field);
		if (!empty($ret)) {
			return json_decode($ret, true);
		}
		
		$sql = "SELECT * FROM cms_manager.anchor_level WHERE level=$level";
		$db_cms = $this->getDbMain();
		$rows = $db_cms->query($sql);
		if (!empty($rows) && $rows->num_rows > 0) {
			$row = $rows->fetch_assoc();
			$redis->hSet($key, $field, json_encode($row));
			return $row;
		}
		
		return null;
	}
	// read anchor_sun_exp_conf from redis or mysql.
	public function get_anchor_sun_exp_conf($level)
	{
		$key = "h_anchor_sun_exp_conf";
		$field = $level . "";
	
		$redis = $this->getRedisMaster();
		$ret = $redis->hGet($key, $field);
		if (!empty($ret)) {
			return json_decode($ret, true);
		}
	
		$sql = "SELECT * FROM raidcall.anchor_level_info WHERE anchor_level=$level";
		$db_raidcall = $this->getDbMain();
		$rows = $db_raidcall->query($sql);
		if (!empty($rows) && $rows->num_rows > 0) {
			$row = $rows->fetch_assoc();
			$redis->hSet($key, $field, json_encode($row));
			return $row;
		}
	
		return null;
	}
	
	public function on_user_consume_exp_add($uid, $level_now, $exp_now, $exp_add)
	{
		$lvl_add = $level_now;
		$exp_final = $exp_add + $exp_now;
		$lvl_inf_now = $this->get_user_consume_conf($lvl_add);
		$lvl_inf_next = $this->get_user_consume_conf($lvl_add + 1);
		
		do {
			if (empty($lvl_inf_now) || empty($lvl_inf_next)) {
				break;
			}
		
			do {
				if ($exp_final < $lvl_inf_now['level_exp']) {
					break;
				}
		
				$exp_final = $exp_final - $lvl_inf_now['level_exp'];
				$lvl_add += 1;
					
				$lvl_inf_now = $lvl_inf_next;
				$lvl_inf_next = $this->get_user_consume_conf($lvl_add + 1);
		
			} while (!empty($lvl_inf_next));
		
		} while (0);
		
		$sql = "UPDATE rcec_main.user_attribute SET con_incen_dedu=con_incen_dedu+$exp_add, gift_consume=gift_consume+$exp_add, consume_level=$lvl_add, consume_exp=$exp_final WHERE uid=$uid";
		$db_main = $this->getDbMain();
		$rows = $db_main->query($sql);
		if (empty($rows) || $db_main->affected_rows == 0) {
			LogApi::logProcess("UserAttributeModel:on_user_consume_exp_add failure. sql:$sql");
		}
		
		$this->cleanCache($uid);
	}
	
	public function on_user_active_exp_add($uid, $level_now, $exp_now, $exp_add)
	{
		$lvl_add = $level_now;
		$exp_final = $exp_add + $exp_now;
		$lvl_inf_now = $this->get_user_active_conf($lvl_add);
		$lvl_inf_next = $this->get_user_active_conf($lvl_add + 1);
		
		do {
			if (empty($lvl_inf_now) || empty($lvl_inf_next)) {
				break;
			}
		
			do {
				if ($exp_final < $lvl_inf_now['level_exp']) {
					break;
				}
		
				$exp_final = $exp_final - $lvl_inf_now['level_exp'];
				$lvl_add += 1;
					
				$lvl_inf_now = $lvl_inf_next;
				$lvl_inf_next = $this->get_user_active_conf($lvl_add + 1);
		
			} while (!empty($lvl_inf_next));
		
		} while (0);

		$sys_parameter = new SysParametersModel();
		$newer_active_level = $sys_parameter->GetSysParameters(205, 'parm1');
		if ($lvl_add >= $newer_active_level) {
 			$sql = "UPDATE rcec_main.user_attribute SET active_point = active_point+$exp_add, active_level = $lvl_add, active_exp=$exp_final, new = 0 WHERE uid = $uid";
//  			$this->clean("disciple:count:down:$uid");
		} else {
 			$sql = "UPDATE rcec_main.user_attribute SET active_point = active_point+$exp_add, active_level = $lvl_add, active_exp=$exp_final WHERE uid = $uid";
		}
		
		$db_main = $this->getDbMain();
		$rows = $db_main->query($sql);
		if (empty($rows) || $db_main->affected_rows == 0) {
			LogApi::logProcess("UserAttributeModel:on_user_active_exp_add failure. sql:$sql");
		}
		
		$this->cleanCache($uid);
	}
	
	public function on_anchor_exp_add($uid, $level_now, $exp_now, $exp_add)
	{
		$lvl_add = $level_now;
		$exp_final = $exp_add + $exp_now;
		$lvl_inf_now = $this->get_anchor_exp_conf($lvl_add);
		$lvl_inf_next = $this->get_anchor_exp_conf($lvl_add + 1);
		
		do {
			if (empty($lvl_inf_now) || empty($lvl_inf_next)) {
				break;
			}
				
			do {
				if ($exp_final < $lvl_inf_now['level_exp']) {
					break;
				}
		
				$exp_final = $exp_final - $lvl_inf_now['level_exp'];
				$lvl_add += 1;
			
				$lvl_inf_now = $lvl_inf_next;
				$lvl_inf_next = $this->get_anchor_exp_conf($lvl_add + 1);
		
			} while (!empty($lvl_inf_next));
				
		} while (0);
		
		$sql = "UPDATE rcec_main.user_attribute SET point_balance=point_balance+$exp_add, experience=experience+$exp_add,experience_level=$lvl_add,channel_point=channel_point+$exp_add,anchor_exp=$exp_final WHERE uid=$uid";
		$db_main = $this->getDbMain();
		$rows = $db_main->query($sql);
		
		if (empty($rows) || $db_main->affected_rows == 0) {
			LogApi::logProcess("UserAttributeModel:on_anchor_exp_add failure. sql:$sql");
		}
		
		$this->cleanCache($uid);
	}
	
	public function on_anchor_sun_exp_add($uid, $level_now, $exp_now, $exp_add)
	{
		$lvl_add = $level_now;
		$exp_final = $exp_add + $exp_now;
		$lvl_inf_now = $this->get_anchor_sun_exp_conf($lvl_add);
		$lvl_inf_next = $this->get_anchor_sun_exp_conf($lvl_add + 1);
		
		do {
			if (empty($lvl_inf_now) || empty($lvl_inf_next)) {
				break;
			}
			
			do {
				if ($exp_final < $lvl_inf_now['level_exp']) {
					break;
				}
				
				$exp_final = $exp_final - $lvl_inf_now['level_exp'];
				$lvl_add += 1;
				
				$lvl_inf_now = $lvl_inf_next;
				$lvl_inf_next = $this->get_anchor_sun_exp_conf($lvl_add + 1);
				
			} while (!empty($lvl_inf_next));
			
		} while (0);
		
        $sql = "UPDATE raidcall.anchor_info SET anchor_current_experience=anchor_current_experience+$exp_add,level_id=$lvl_add,anchor_curr_exp=$exp_final WHERE uid=$uid";
        $db_raidcall = $this->getDbRaidcall();
        $rows = $db_raidcall->query($sql);
        
        if (empty($rows) || $db_raidcall->affected_rows == 0) {
        	LogApi::logProcess("UserAttributeModel:on_anchor_sun_exp_add failure. sql:$sql");
        }
        
        // no cache. no need clean.
	}
}

?>

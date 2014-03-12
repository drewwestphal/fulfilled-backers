-- phpMyAdmin SQL Dump
-- version 3.4.0
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 11, 2014 at 07:36 PM
-- Server version: 5.5.34
-- PHP Version: 5.3.25

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cmsw`
--

-- --------------------------------------------------------

--
-- Table structure for table `addonproducts`
--

CREATE TABLE IF NOT EXISTS `addonproducts` (
  `AddOnID` int(11) NOT NULL AUTO_INCREMENT,
  `BackerID` char(7) COLLATE utf8_unicode_ci NOT NULL,
  `ProductID` char(50) COLLATE utf8_unicode_ci NOT NULL,
  `AddOnQty` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`AddOnID`),
  KEY `BackerID` (`BackerID`),
  KEY `ProductID` (`ProductID`),
  KEY `AddOnQty` (`AddOnQty`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=345 ;

-- --------------------------------------------------------

--
-- Table structure for table `backermeta`
--

CREATE TABLE IF NOT EXISTS `backermeta` (
  `BackerID` char(7) COLLATE utf8_unicode_ci NOT NULL,
  `BackerMetaKey` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `BackerMetaValue` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BackerBatch` char(40) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`BackerID`,`BackerMetaKey`),
  KEY `BackerMetaValue` (`BackerMetaValue`),
  KEY `BackerBatch` (`BackerBatch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backers`
--

CREATE TABLE IF NOT EXISTS `backers` (
  `BackerID` char(7) COLLATE utf8_unicode_ci NOT NULL,
  `BackerName` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BackerEmail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `BackerPledgeAmount` decimal(10,2) NOT NULL,
  `BackerPledgeStatus` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `TierID` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `BackerBatch` char(40) COLLATE utf8_unicode_ci NOT NULL,
  `BackerShippingCategory` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `BackerAccessToken` char(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`BackerID`),
  UNIQUE KEY `BackerSiteKey` (`BackerAccessToken`),
  KEY `TierID` (`TierID`),
  KEY `BackerBatch` (`BackerBatch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `basekitsummary`
--

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `cmsw`.`basekitsummary` AS select `cmsw`.`basekits`.`BaseKitName` AS `BaseKitName`,count(`cmsw`.`basekits`.`BaseKitIdx`) AS `BaseKitNumPieces` from `cmsw`.`basekits` group by `cmsw`.`basekits`.`BaseKitName`;

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `ConfigName` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `CurrentBatch` char(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ConfigName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dl_clicks`
--

CREATE TABLE IF NOT EXISTS `dl_clicks` (
  `ClickID` int(11) NOT NULL AUTO_INCREMENT,
  `LinkKey` char(30) COLLATE utf8_unicode_ci NOT NULL,
  `IPAddress` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `ClickTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ClickID`),
  KEY `LinkKey` (`LinkKey`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8539 ;

-- --------------------------------------------------------

--
-- Table structure for table `dl_files`
--

CREATE TABLE IF NOT EXISTS `dl_files` (
  `FileID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `AWSPath` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `FileDisplayName` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `FileSortOrder` int(11) NOT NULL DEFAULT '9999999',
  PRIMARY KEY (`FileID`),
  UNIQUE KEY `DisplayName` (`FileDisplayName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- Table structure for table `dl_links`
--

CREATE TABLE IF NOT EXISTS `dl_links` (
  `LinkKey` char(30) COLLATE utf8_unicode_ci NOT NULL,
  `FileID` int(10) unsigned DEFAULT NULL,
  `MaxClick` tinyint(4) NOT NULL DEFAULT '5',
  `OwnerKey` char(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `LinkCreatedTimestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DisplayLink` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`LinkKey`),
  KEY `FileID` (`FileID`),
  KEY `OwnerKey` (`OwnerKey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `dl_linksNJ`
--
CREATE TABLE IF NOT EXISTS `dl_linksNJ` (
`LinkKey` char(30)
,`FileID` int(10) unsigned
,`MaxClick` tinyint(4)
,`OwnerKey` char(100)
,`LinkCreatedTimestamp` timestamp
,`DisplayLink` tinyint(1)
,`BackerID` char(100)
);
-- --------------------------------------------------------

--
-- Table structure for table `dl_redemptioncodes`
--

CREATE TABLE IF NOT EXISTS `dl_redemptioncodes` (
  `ProductID` char(50) COLLATE utf8_unicode_ci NOT NULL,
  `RedemptionCode` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `BackerID` char(7) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RedemptionCodeIsPublic` tinyint(4) NOT NULL DEFAULT '1',
  `RedemptionCodeNotes` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ProductID`,`RedemptionCode`),
  KEY `RedemptionCode` (`RedemptionCode`),
  KEY `ProductID` (`ProductID`),
  KEY `BackerID` (`BackerID`),
  KEY `RedemptionCodeIsPublic` (`RedemptionCodeIsPublic`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `EmailSelectionQueries`
--

CREATE TABLE IF NOT EXISTS `EmailSelectionQueries` (
  `qid` int(11) NOT NULL AUTO_INCREMENT,
  `q` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `email_field` char(32) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Email',
  `toname_field` char(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `addl_term_func` char(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `memo` char(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`qid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=59 ;

-- --------------------------------------------------------

--
-- Table structure for table `EmailSenders`
--

CREATE TABLE IF NOT EXISTS `EmailSenders` (
  `SenderID` int(11) NOT NULL AUTO_INCREMENT,
  `SenderAddress` char(128) COLLATE utf8_unicode_ci NOT NULL,
  `SenderName` char(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`SenderID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `EmailTemplates`
--

CREATE TABLE IF NOT EXISTS `EmailTemplates` (
  `etid` int(11) NOT NULL AUTO_INCREMENT,
  `etname` char(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `etsubj` char(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ettext` text COLLATE utf8_unicode_ci,
  `ethtml` text COLLATE utf8_unicode_ci,
  `etcat` enum('Default','System') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Default',
  `etlastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `etsender` int(11) NOT NULL DEFAULT '2',
  PRIMARY KEY (`etid`),
  UNIQUE KEY `ETName` (`etname`),
  KEY `etsender` (`etsender`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=45 ;

-- --------------------------------------------------------

--
-- Table structure for table `packtypes`
--

CREATE TABLE IF NOT EXISTS `packtypes` (
  `PackType` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `OrderPermutation` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `PackTypeBackerDesc` varchar(5000) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`PackType`),
  UNIQUE KEY `OrderPermutation` (`OrderPermutation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productfilecorrelations`
--

CREATE TABLE IF NOT EXISTS `productfilecorrelations` (
  `ProductID` char(50) COLLATE utf8_unicode_ci NOT NULL,
  `FileDisplayName` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ProductID`,`FileDisplayName`),
  KEY `FileDisplayName` (`FileDisplayName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `ProductID` char(50) COLLATE utf8_unicode_ci NOT NULL,
  `IsPhysical` tinyint(1) NOT NULL,
  `FulfillmentChannel` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `ShipmentGrouping` char(24) COLLATE utf8_unicode_ci NOT NULL,
  `DataRequirements` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Notes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ProductDisplayName` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `ShipwireSKU` char(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ProductID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productsbybacker`
--

CREATE TABLE IF NOT EXISTS `productsbybacker` (
  `BackerID` char(7) COLLATE utf8_unicode_ci NOT NULL,
  `ProductID` char(50) COLLATE utf8_unicode_ci NOT NULL,
  `SkuResNotes` char(24) COLLATE utf8_unicode_ci DEFAULT NULL,
  `OwedQty` int(11) NOT NULL,
  `CreatedByBatch` char(40) COLLATE utf8_unicode_ci NOT NULL,
  `DeliveredStatus` enum('pending','shipped') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'pending',
  PRIMARY KEY (`BackerID`,`ProductID`),
  KEY `ProductID` (`ProductID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productsbytier`
--

CREATE TABLE IF NOT EXISTS `productsbytier` (
  `TierID` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `ProductID` char(50) COLLATE utf8_unicode_ci NOT NULL,
  `DefaultQty` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`TierID`,`ProductID`),
  KEY `ProductID` (`ProductID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tiers`
--

CREATE TABLE IF NOT EXISTS `tiers` (
  `TierID` char(20) COLLATE utf8_unicode_ci NOT NULL,
  `TierDescName` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `TierFullText` varchar(2000) COLLATE utf8_unicode_ci NOT NULL,
  `TierMinPledge` decimal(10,2) NOT NULL,
  `TierShippingMeta` char(20) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `TierIntlSurcharge` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`TierID`),
  UNIQUE KEY `TierDescName` (`TierDescName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `_AllFulfillHouseOrders`
--
CREATE TABLE IF NOT EXISTS `_AllFulfillHouseOrders` (
`OrderPermutation` text
,`BackerID` char(7)
,`NumProds` decimal(32,0)
,`PackType` char(20)
,`PackTypeShort` varchar(52)
,`FulfillOrderHasShipped` int(1)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_AllShippingAddresses`
--
CREATE TABLE IF NOT EXISTS `_AllShippingAddresses` (
`BackerID` char(7)
,`ShipName` varchar(255)
,`ShipLine_1` varchar(255)
,`ShipLine_2` varchar(255)
,`ShipCity` varchar(255)
,`ShipStateProvince` varchar(255)
,`ShipPostCode` varchar(255)
,`ShipCountry` varchar(255)
,`IntlPhone` varchar(255)
,`ActualShippingCategory` varchar(13)
,`AddressLocked` int(1)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_AllShirtSizes`
--
CREATE TABLE IF NOT EXISTS `_AllShirtSizes` (
`BackerID` char(7)
,`ShirtSize` varchar(255)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_ChannelSummary`
--
CREATE TABLE IF NOT EXISTS `_ChannelSummary` (
`BackerID` char(7)
,`FulfillmentHouse` int(1)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_ComputeAddOnRewards`
--
CREATE TABLE IF NOT EXISTS `_ComputeAddOnRewards` (
`BackerID` char(7)
,`ProductID` char(50)
,`AddOnQty` int(11)
,`BackerBatch` char(40)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_ComputeRewardsByTier`
--
CREATE TABLE IF NOT EXISTS `_ComputeRewardsByTier` (
`BackerID` char(7)
,`ProductID` char(50)
,`DefaultQty` int(11)
,`BackerBatch` char(40)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_OrderSummary`
--
CREATE TABLE IF NOT EXISTS `_OrderSummary` (
`BackerID` char(7)
,`NumProducts` bigint(21)
,`NumNonPhysical` decimal(26,0)
,`NumPhysical` decimal(25,0)
,`BackerPledgeAmount` decimal(10,2)
,`ProjectedShipType` varchar(20)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_ProductSummary`
--
CREATE TABLE IF NOT EXISTS `_ProductSummary` (
`ProductID` char(50)
,`IsPhysical` tinyint(1)
,`FulfillmentChannel` char(20)
,`NumOrdered` decimal(32,0)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_ShippingManifest`
--
CREATE TABLE IF NOT EXISTS `_ShippingManifest` (
`BackerID` char(7)
,`PackType` char(20)
,`PackTypeShort` varchar(52)
,`ShipName` varchar(255)
,`ShipLine_1` varchar(255)
,`ShipLine_2` varchar(255)
,`ShipCity` varchar(255)
,`ShipStateProvince` varchar(255)
,`ShipPostCode` varchar(255)
,`ShipCountry` varchar(255)
,`IntlPhone` varchar(255)
,`ShirtSize` varchar(255)
,`MissingIntlPhone` int(1)
,`MissingShirtSize` int(1)
,`AddressLocked` int(1)
,`FulfillOrderHasShipped` int(1)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_zFulfillHouseOrderHelper`
--
CREATE TABLE IF NOT EXISTS `_zFulfillHouseOrderHelper` (
`BackerID` char(7)
,`PhysicalProductOrdered` varchar(62)
,`NumProd` int(11)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_zFulfillHouseOrderHelper2`
--
CREATE TABLE IF NOT EXISTS `_zFulfillHouseOrderHelper2` (
`BackerID` char(7)
,`OrderPermutation` text
,`NumProds` decimal(32,0)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_zFulfillHouseOrderHelper3`
--
CREATE TABLE IF NOT EXISTS `_zFulfillHouseOrderHelper3` (
`NumOrders` bigint(21)
,`NProdsInOrder` decimal(32,0)
,`OrderPermutation` text
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_zPhysOrderHelper`
--
CREATE TABLE IF NOT EXISTS `_zPhysOrderHelper` (
`BackerID` char(7)
,`PhysicalProductOrdered` varchar(62)
,`NumProd` int(11)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_zPhysOrderHelper2`
--
CREATE TABLE IF NOT EXISTS `_zPhysOrderHelper2` (
`BackerID` char(7)
,`OrderPermutation` text
,`NumProds` decimal(32,0)
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_zPhysOrderHelper3`
--
CREATE TABLE IF NOT EXISTS `_zPhysOrderHelper3` (
`NumOrders` bigint(21)
,`NProdsInOrder` decimal(32,0)
,`OrderPermutation` text
);
-- --------------------------------------------------------

--
-- Stand-in structure for view `_zSampleAddresses`
--
CREATE TABLE IF NOT EXISTS `_zSampleAddresses` (
`Address1` varchar(128)
,`Address2` varchar(128)
,`CityTown` varchar(128)
,`State` varchar(128)
,`ZipCode` char(12)
,`ISO2Char` char(2)
,`DestCategory` varchar(13)
);
-- --------------------------------------------------------

--
-- Structure for view `dl_linksNJ`
--
DROP TABLE IF EXISTS `dl_linksNJ`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `dl_linksNJ` AS select `dl_links`.`LinkKey` AS `LinkKey`,`dl_links`.`FileID` AS `FileID`,`dl_links`.`MaxClick` AS `MaxClick`,`dl_links`.`OwnerKey` AS `OwnerKey`,`dl_links`.`LinkCreatedTimestamp` AS `LinkCreatedTimestamp`,`dl_links`.`DisplayLink` AS `DisplayLink`,`dl_links`.`OwnerKey` AS `BackerID` from `dl_links`;

-- --------------------------------------------------------

--
-- Structure for view `_AllFulfillHouseOrders`
--
DROP TABLE IF EXISTS `_AllFulfillHouseOrders`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `_AllFulfillHouseOrders` AS select `_zFulfillHouseOrderHelper2`.`OrderPermutation` AS `OrderPermutation`,`_zFulfillHouseOrderHelper2`.`BackerID` AS `BackerID`,`_zFulfillHouseOrderHelper2`.`NumProds` AS `NumProds`,`packtypes`.`PackType` AS `PackType`,substr(`packtypes`.`PackType`,9) AS `PackTypeShort`,(sum((`productsbybacker`.`DeliveredStatus` = 'shipped')) = `_zFulfillHouseOrderHelper2`.`NumProds`) AS `FulfillOrderHasShipped` from ((`_zFulfillHouseOrderHelper2` join `packtypes` on((`_zFulfillHouseOrderHelper2`.`OrderPermutation` = `packtypes`.`OrderPermutation`))) join `productsbybacker` on((`_zFulfillHouseOrderHelper2`.`BackerID` = `productsbybacker`.`BackerID`))) where (`_zFulfillHouseOrderHelper2`.`OrderPermutation` like concat('%',`productsbybacker`.`ProductID`,'%')) group by `_zFulfillHouseOrderHelper2`.`BackerID`;

-- --------------------------------------------------------

--
-- Structure for view `_AllShippingAddresses`
--
DROP TABLE IF EXISTS `_AllShippingAddresses`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_AllShippingAddresses` AS select `b`.`BackerID` AS `BackerID`,`bm1`.`BackerMetaValue` AS `ShipName`,`bm2`.`BackerMetaValue` AS `ShipLine_1`,`bm25`.`BackerMetaValue` AS `ShipLine_2`,`bm3`.`BackerMetaValue` AS `ShipCity`,`bm4`.`BackerMetaValue` AS `ShipStateProvince`,`bm5`.`BackerMetaValue` AS `ShipPostCode`,`bm6`.`BackerMetaValue` AS `ShipCountry`,`bm7`.`BackerMetaValue` AS `IntlPhone`,if((`bm6`.`BackerMetaValue` = 'United States'),'Domestic',if((`bm6`.`BackerMetaValue` = 'Canada'),'Canada','International')) AS `ActualShippingCategory`,(`bm8`.`BackerMetaValue` is not null) AS `AddressLocked` from (((((((((`backers` `b` join `backermeta` `bm1` on(((`b`.`BackerID` = `bm1`.`BackerID`) and (`bm1`.`BackerMetaKey` = _utf8'Name')))) join `backermeta` `bm2` on(((`b`.`BackerID` = `bm2`.`BackerID`) and (`bm2`.`BackerMetaKey` = _utf8'Line_1')))) left join `backermeta` `bm25` on(((`b`.`BackerID` = `bm25`.`BackerID`) and (`bm25`.`BackerMetaKey` = _utf8'Line_2')))) join `backermeta` `bm3` on(((`b`.`BackerID` = `bm3`.`BackerID`) and (`bm3`.`BackerMetaKey` = _utf8'City')))) left join `backermeta` `bm4` on(((`b`.`BackerID` = `bm4`.`BackerID`) and (`bm4`.`BackerMetaKey` = _utf8'State')))) left join `backermeta` `bm5` on(((`b`.`BackerID` = `bm5`.`BackerID`) and (`bm5`.`BackerMetaKey` = _utf8'Postal Code')))) join `backermeta` `bm6` on(((`b`.`BackerID` = `bm6`.`BackerID`) and (`bm6`.`BackerMetaKey` = _utf8'Country')))) left join `backermeta` `bm7` on(((`b`.`BackerID` = `bm7`.`BackerID`) and (`bm7`.`BackerMetaKey` = _utf8'IntlPhone')))) left join `backermeta` `bm8` on(((`b`.`BackerID` = `bm8`.`BackerID`) and (`bm8`.`BackerMetaKey` = _utf8'ShipLocked'))));

-- --------------------------------------------------------

--
-- Structure for view `_AllShirtSizes`
--
DROP TABLE IF EXISTS `_AllShirtSizes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_AllShirtSizes` AS select `backermeta`.`BackerID` AS `BackerID`,`backermeta`.`BackerMetaValue` AS `ShirtSize` from `backermeta` where (`backermeta`.`BackerMetaKey` like 'Choices');

-- --------------------------------------------------------

--
-- Structure for view `_ChannelSummary`
--
DROP TABLE IF EXISTS `_ChannelSummary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_ChannelSummary` AS select `backers`.`BackerID` AS `BackerID`,(`products`.`FulfillmentChannel` like 'FulfillmentHouse') AS `FulfillmentHouse` from ((`backers` left join `productsbybacker` on((`backers`.`BackerID` = `productsbybacker`.`BackerID`))) join `products` on((`productsbybacker`.`ProductID` = `products`.`ProductID`))) group by `backers`.`BackerID`;

-- --------------------------------------------------------

--
-- Structure for view `_ComputeAddOnRewards`
--
DROP TABLE IF EXISTS `_ComputeAddOnRewards`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_ComputeAddOnRewards` AS select `backers`.`BackerID` AS `BackerID`,`addonproducts`.`ProductID` AS `ProductID`,`addonproducts`.`AddOnQty` AS `AddOnQty`,`backers`.`BackerBatch` AS `BackerBatch` from (`backers` join `addonproducts` on((`backers`.`BackerID` = `addonproducts`.`BackerID`)));

-- --------------------------------------------------------

--
-- Structure for view `_ComputeRewardsByTier`
--
DROP TABLE IF EXISTS `_ComputeRewardsByTier`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_ComputeRewardsByTier` AS select `backers`.`BackerID` AS `BackerID`,`productsbytier`.`ProductID` AS `ProductID`,`productsbytier`.`DefaultQty` AS `DefaultQty`,`backers`.`BackerBatch` AS `BackerBatch` from (`backers` join `productsbytier` on((`backers`.`TierID` = `productsbytier`.`TierID`)));

-- --------------------------------------------------------

--
-- Structure for view `_OrderSummary`
--
DROP TABLE IF EXISTS `_OrderSummary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `_OrderSummary` AS select `productsbybacker`.`BackerID` AS `BackerID`,count(`productsbybacker`.`BackerID`) AS `NumProducts`,(count(`productsbybacker`.`BackerID`) - sum(`products`.`IsPhysical`)) AS `NumNonPhysical`,sum(`products`.`IsPhysical`) AS `NumPhysical`,`backers`.`BackerPledgeAmount` AS `BackerPledgeAmount`,ifnull(`_AllShippingAddresses`.`ActualShippingCategory`,if((`backers`.`BackerShippingCategory` = _utf8''),`tiers`.`TierShippingMeta`,`backers`.`BackerShippingCategory`)) AS `ProjectedShipType` from ((((`productsbybacker` join `backers` on((`productsbybacker`.`BackerID` = `backers`.`BackerID`))) join `products` on((`productsbybacker`.`ProductID` = `products`.`ProductID`))) join `tiers` on((`backers`.`TierID` = `tiers`.`TierID`))) left join `_AllShippingAddresses` on((`productsbybacker`.`BackerID` = `_AllShippingAddresses`.`BackerID`))) group by `productsbybacker`.`BackerID`;

-- --------------------------------------------------------

--
-- Structure for view `_ProductSummary`
--
DROP TABLE IF EXISTS `_ProductSummary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `_ProductSummary` AS select `p`.`ProductID` AS `ProductID`,`p`.`IsPhysical` AS `IsPhysical`,`p`.`FulfillmentChannel` AS `FulfillmentChannel`,sum(`productsbybacker`.`OwedQty`) AS `NumOrdered` from (`productsbybacker` join `products` `p` on((`productsbybacker`.`ProductID` = `p`.`ProductID`))) group by `p`.`ProductID` order by sum(`productsbybacker`.`OwedQty`) desc;

-- --------------------------------------------------------

--
-- Structure for view `_ShippingManifest`
--
DROP TABLE IF EXISTS `_ShippingManifest`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_ShippingManifest` AS select `_AllFulfillHouseOrders`.`BackerID` AS `BackerID`,`_AllFulfillHouseOrders`.`PackType` AS `PackType`,`_AllFulfillHouseOrders`.`PackTypeShort` AS `PackTypeShort`,`_AllShippingAddresses`.`ShipName` AS `ShipName`,`_AllShippingAddresses`.`ShipLine_1` AS `ShipLine_1`,`_AllShippingAddresses`.`ShipLine_2` AS `ShipLine_2`,`_AllShippingAddresses`.`ShipCity` AS `ShipCity`,`_AllShippingAddresses`.`ShipStateProvince` AS `ShipStateProvince`,`_AllShippingAddresses`.`ShipPostCode` AS `ShipPostCode`,`_AllShippingAddresses`.`ShipCountry` AS `ShipCountry`,`_AllShippingAddresses`.`IntlPhone` AS `IntlPhone`,`_AllShirtSizes`.`ShirtSize` AS `ShirtSize`,((`_AllShippingAddresses`.`ShipCountry` <> 'United States') and isnull(`_AllShippingAddresses`.`IntlPhone`)) AS `MissingIntlPhone`,((`_AllFulfillHouseOrders`.`OrderPermutation` like '%Tshirt%') and isnull(`_AllShirtSizes`.`ShirtSize`)) AS `MissingShirtSize`,`_AllShippingAddresses`.`AddressLocked` AS `AddressLocked`,`_AllFulfillHouseOrders`.`FulfillOrderHasShipped` AS `FulfillOrderHasShipped` from ((`_AllFulfillHouseOrders` join `_AllShippingAddresses` on((`_AllFulfillHouseOrders`.`BackerID` = `_AllShippingAddresses`.`BackerID`))) left join `_AllShirtSizes` on((`_AllFulfillHouseOrders`.`BackerID` = `_AllShirtSizes`.`BackerID`)));

-- --------------------------------------------------------

--
-- Structure for view `_zFulfillHouseOrderHelper`
--
DROP TABLE IF EXISTS `_zFulfillHouseOrderHelper`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_zFulfillHouseOrderHelper` AS select `productsbybacker`.`BackerID` AS `BackerID`,concat(`productsbybacker`.`OwedQty`,_utf8'*',`productsbybacker`.`ProductID`) AS `PhysicalProductOrdered`,`productsbybacker`.`OwedQty` AS `NumProd` from (`productsbybacker` join `products` on((`productsbybacker`.`ProductID` = `products`.`ProductID`))) where ((`products`.`IsPhysical` = 1) and (`products`.`FulfillmentChannel` = 'FulfillmentHouse'));

-- --------------------------------------------------------

--
-- Structure for view `_zFulfillHouseOrderHelper2`
--
DROP TABLE IF EXISTS `_zFulfillHouseOrderHelper2`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_zFulfillHouseOrderHelper2` AS select `_zFulfillHouseOrderHelper`.`BackerID` AS `BackerID`,group_concat(`_zFulfillHouseOrderHelper`.`PhysicalProductOrdered` order by `_zFulfillHouseOrderHelper`.`PhysicalProductOrdered` ASC separator ',') AS `OrderPermutation`,sum(`_zFulfillHouseOrderHelper`.`NumProd`) AS `NumProds` from `_zFulfillHouseOrderHelper` group by `_zFulfillHouseOrderHelper`.`BackerID`;

-- --------------------------------------------------------

--
-- Structure for view `_zFulfillHouseOrderHelper3`
--
DROP TABLE IF EXISTS `_zFulfillHouseOrderHelper3`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_zFulfillHouseOrderHelper3` AS select count(`_zFulfillHouseOrderHelper2`.`BackerID`) AS `NumOrders`,`_zFulfillHouseOrderHelper2`.`NumProds` AS `NProdsInOrder`,`_zFulfillHouseOrderHelper2`.`OrderPermutation` AS `OrderPermutation` from `_zFulfillHouseOrderHelper2` group by `_zFulfillHouseOrderHelper2`.`OrderPermutation` order by count(`_zFulfillHouseOrderHelper2`.`BackerID`) desc;

-- --------------------------------------------------------

--
-- Structure for view `_zPhysOrderHelper`
--
DROP TABLE IF EXISTS `_zPhysOrderHelper`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_zPhysOrderHelper` AS select `productsbybacker`.`BackerID` AS `BackerID`,concat(`productsbybacker`.`OwedQty`,_utf8'*',`productsbybacker`.`ProductID`) AS `PhysicalProductOrdered`,`productsbybacker`.`OwedQty` AS `NumProd` from (`productsbybacker` join `products` on((`productsbybacker`.`ProductID` = `products`.`ProductID`))) where (`products`.`IsPhysical` = 1) order by concat(`productsbybacker`.`OwedQty`,_utf8'*',`productsbybacker`.`ProductID`);

-- --------------------------------------------------------

--
-- Structure for view `_zPhysOrderHelper2`
--
DROP TABLE IF EXISTS `_zPhysOrderHelper2`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_zPhysOrderHelper2` AS select `_zPhysOrderHelper`.`BackerID` AS `BackerID`,group_concat(`_zPhysOrderHelper`.`PhysicalProductOrdered` order by `_zPhysOrderHelper`.`PhysicalProductOrdered` ASC separator ',') AS `OrderPermutation`,sum(`_zPhysOrderHelper`.`NumProd`) AS `NumProds` from `_zPhysOrderHelper` group by `_zPhysOrderHelper`.`BackerID`;

-- --------------------------------------------------------

--
-- Structure for view `_zPhysOrderHelper3`
--
DROP TABLE IF EXISTS `_zPhysOrderHelper3`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_zPhysOrderHelper3` AS select count(`_zPhysOrderHelper2`.`BackerID`) AS `NumOrders`,`_zPhysOrderHelper2`.`NumProds` AS `NProdsInOrder`,`_zPhysOrderHelper2`.`OrderPermutation` AS `OrderPermutation` from `_zPhysOrderHelper2` group by `_zPhysOrderHelper2`.`OrderPermutation` order by count(`_zPhysOrderHelper2`.`BackerID`) desc;

-- --------------------------------------------------------

--
-- Structure for view `_zSampleAddresses`
--
DROP TABLE IF EXISTS `_zSampleAddresses`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`%` SQL SECURITY DEFINER VIEW `_zSampleAddresses` AS select `cd`.`Address1` AS `Address1`,`cd`.`Address2` AS `Address2`,`cd`.`CityTown` AS `CityTown`,`cd`.`State` AS `State`,`cd`.`ZipCode` AS `ZipCode`,`ct`.`ISO2Char` AS `ISO2Char`,if((`ct`.`ISO2Char` = 'US'),'Domestic',if((`ct`.`ISO2Char` = 'CA'),'Canada','International')) AS `DestCategory` from (`sfsurvey`.`customerdata` `cd` join `sfsurvey`.`CountryTransl` `ct` on((`cd`.`Country` = `ct`.`CustomerCountryName`))) where ((`cd`.`Address1` is not null) and (`cd`.`PurchaseCompleted` = 1)) order by if((`ct`.`ISO2Char` = 'US'),'Domestic',if((`ct`.`ISO2Char` = 'CA'),'Canada','International'));

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addonproducts`
--
ALTER TABLE `addonproducts`
  ADD CONSTRAINT `addonproducts_ibfk_1` FOREIGN KEY (`BackerID`) REFERENCES `backers` (`BackerID`) ON DELETE CASCADE,
  ADD CONSTRAINT `addonproducts_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON UPDATE CASCADE;

--
-- Constraints for table `backermeta`
--
ALTER TABLE `backermeta`
  ADD CONSTRAINT `backermeta_ibfk_1` FOREIGN KEY (`BackerID`) REFERENCES `backers` (`BackerID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `backers`
--
ALTER TABLE `backers`
  ADD CONSTRAINT `backers_ibfk_1` FOREIGN KEY (`TierID`) REFERENCES `tiers` (`TierID`) ON UPDATE CASCADE;

--
-- Constraints for table `dl_clicks`
--
ALTER TABLE `dl_clicks`
  ADD CONSTRAINT `dl_clicks_ibfk_1` FOREIGN KEY (`LinkKey`) REFERENCES `dl_links` (`LinkKey`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dl_links`
--
ALTER TABLE `dl_links`
  ADD CONSTRAINT `dl_links_ibfk_1` FOREIGN KEY (`FileID`) REFERENCES `dl_files` (`FileID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dl_links_ibfk_2` FOREIGN KEY (`OwnerKey`) REFERENCES `backers` (`BackerID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dl_redemptioncodes`
--
ALTER TABLE `dl_redemptioncodes`
  ADD CONSTRAINT `dl_redemptioncodes_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `dl_redemptioncodes_ibfk_2` FOREIGN KEY (`BackerID`) REFERENCES `backers` (`BackerID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `EmailTemplates`
--
ALTER TABLE `EmailTemplates`
  ADD CONSTRAINT `EmailTemplates_ibfk_1` FOREIGN KEY (`etsender`) REFERENCES `EmailSenders` (`SenderID`);

--
-- Constraints for table `productfilecorrelations`
--
ALTER TABLE `productfilecorrelations`
  ADD CONSTRAINT `productfilecorrelations_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `productfilecorrelations_ibfk_2` FOREIGN KEY (`FileDisplayName`) REFERENCES `dl_files` (`FileDisplayName`);

--
-- Constraints for table `productsbybacker`
--
ALTER TABLE `productsbybacker`
  ADD CONSTRAINT `productsbybacker_ibfk_1` FOREIGN KEY (`BackerID`) REFERENCES `backers` (`BackerID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `productsbybacker_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON UPDATE CASCADE;

--
-- Constraints for table `productsbytier`
--
ALTER TABLE `productsbytier`
  ADD CONSTRAINT `productsbytier_ibfk_3` FOREIGN KEY (`TierID`) REFERENCES `tiers` (`TierID`) ON UPDATE CASCADE,
  ADD CONSTRAINT `productsbytier_ibfk_4` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

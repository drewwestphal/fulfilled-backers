<?
require_once '../lib/common.php';
require_once './query.php';
?>

<html>
	<head>

		<title>stats</title>
		<style type="text/css">
			body {
				margin-left: 40px;
				margin-top: 33px;
			}

			h6 {
				margin: 15px 0 0;
			}
			.on, .off {
				font-size: 130%;
				font-weight: bold;
			}
			.on {
				color: red;
			}
			.off {
				color: blue;
			}
			.qtable {
				font-size: 12px;
				font-family: Courier;
				text-align: left;
				border-collapse: collapse;
				border: 1px solid #69c;
			}
			.qtable td, th {
				padding: 2px 5px;
			}
			.qtable th {
				border-bottom: 1px dashed #69c;
				font-size: 14px;
			}
			.qtable tbody tr:hover td {
				background: #d0dafd;
			}
		</style>

	</head>
	<body>
		<h1>CMSW Reports</h1>

		<h3> Order Breakdown </h3>
		<?resToTable("SELECT Count(BackerID) as TotalNumBackers, SUM(BackerPledgeAmount) as TotalPledgedBeforeFees, SUM(BackerPledgeStatus<>'collected') as PledgesRemainingToCollect FROM `backers`", 'qtable', true); ?>
		<br>
		<?resToTable("SELECT COUNT(BackerID) as NumOrders, SUM(NumProducts) as TotalProducts, SUM(NumNonPhysical) as TotalNonPhysical, SUM(NumPhysical) as TotalPhysical, SUM(NumPhysical=0) as NumNonPhysicalOrders, SUM(NumPhysical<>0) as NumPhysicalOrders, AVG(BackerPledgeAmount) AveragePledgeAmount FROM `_OrderSummary`", 'qtable', true); ?>
		<br>
		<?resToTable("SELECT ProjectedShipType, COUNT(BackerID) as NumOrders, SUM(NumPhysical) as NumPieces, SUM(NumPhysical)/COUNT(BackerID) as AvgPcsPerOrder FROM `_OrderSummary` WHERE `NumPhysical` != 0 GROUP BY ProjectedShipType ORDER BY NumOrders DESC"); ?>
		<br>
		<br/>

        <h3>Survey Response Rate </h3>
        <?resToTable("SELECT  '# Fulfill House Orders'Item, @total := COUNT( DISTINCT BackerID ) NumBackers,  '100%' AS Percent
FROM productsbybacker
NATURAL JOIN products
WHERE FulfillmentChannel LIKE  'FulfillmentHouse'
UNION ALL 
SELECT  '# Addresses Received', COUNT( DISTINCT BackerID ) , CONCAT( FORMAT( (
COUNT( DISTINCT BackerID ) / @total ) *100, 2 ) ,  '%'
)
FROM productsbybacker
NATURAL JOIN products
NATURAL JOIN backermeta
WHERE BackerMetaKey LIKE  'Line_1'
AND FulfillmentChannel LIKE  'FulfillmentHouse'
UNION ALL 
SELECT '# of Tshirt Orders', @total:=SUM(OwedQty), '100%' from productsbybacker where ProductID='Tshirt'
UNION ALL 
SELECT '# of Tshirt Responses' ,COUNT(*),
CONCAT( FORMAT( (
COUNT(*) / @total ) *100, 2 ) ,  '%'
) from _AllShirtSizes
");
        ?>
        <br/>
        <h3>DL Code Redemption Rate </h3>
        <?resToTable("SELECT ProductID, Count(BackerID) as NumRedeemed, CONCAT(TRUNCATE((Count(BackerID) /8363)*100,2),'%') RedemptionPercent FROM `dl_redemptioncodes` WHERE RedemptionCodeIsPublic and BackerID is not null GROUP BY ProductID
");
        ?>
        <br/>
		<h3>Shirt Sizes </h3>
		<?resToTable("SELECT ShirtSize, COUNT( BackerID ) NumShirts
FROM  `_AllShirtSizes`
GROUP BY ShirtSize
ORDER BY FIELD( ShirtSize,  'Men\'s Small',  'Men\'s Medium',  'Men\'s Large',  'Men\'s XL',  'Men\'s XXL',  'Men\'s 3X',  'Men\'s 4X',  'Men\'s 5X',  'Men\'s 6X',  'Ladies\' XS',  'Ladies\' Small',  'Ladies\' Medium',  'Ladies\' Large',  'Ladies\' XL',  'Ladies\' XXL',  'Ladies\' 3X', 'Ladies\' 4X' ) ");
		?>
		<br/>
		<h3>Shipments By Country  </h3>
		<?resToTable("SELECT ShipCountry, COUNT(distinct BackerID) as NumBackers FROM `productsbybacker` natural join products natural join _AllShippingAddresses where FulfillmentChannel like 'FulfillmentHouse' GROUP BY ShipCountry ORDER BY NumBackers DESC");
		?>
		<br/>
		<h3>Physical Product Summary</h3>
		<?resToTable("SELECT *  FROM `_ProductSummary` WHERE `IsPhysical` = 1"); ?>
		<br/>
		<br/>

		<h3> Non-Physical Product Summary </h3>
		<?resToTable("SELECT *  FROM `_ProductSummary` WHERE `IsPhysical` = 0"); ?>
		<br/>
		<br/>

		<h3>Fulfillment House Specific Stats</h3>
		<?resToTable("SELECT PackType, Count(BackerID) as NumOrders, SUM(ShipLine_1 is not null and not (MissingIntlPhone or MissingShirtSize)) as NumReadyToShip, SUM(ShipLine_1 is not null and not (MissingIntlPhone or MissingShirtSize)) / Count(BackerID) as ReadyToShipPercent,
SUM(ShipLine_1 is not null and not (MissingIntlPhone or MissingShirtSize) and ShipCountry = 'United States') DomesticOrdersToShip,
SUM(ShipLine_1 is not null and not (MissingIntlPhone or MissingShirtSize) and ShipCountry <> 'United States') IntlOrdersToShip,
NumProds NumItemsThisPackType   ,
OrderPermutation
FROM  `_AllFulfillHouseOrders` 
NATURAL LEFT JOIN _ShippingManifest

GROUP BY PackType Order by PackType
"); ?>
        <br/>
        <br/>
        <?resToTable("SELECT ProductID, NumOrdered NumberRequired
FROM  `_ProductSummary` 
WHERE  `FulfillmentChannel` LIKE  'FulfillmentHouse'
"); ?>
        <br/>
        <br/>
        <h4>Still Missing Shipping Address</h4>
        <?resToTable("SELECT BackerID, BackerEmail, BackerPledgeAmount, BackerAccessToken
FROM _AllFulfillHouseOrders
NATURAL JOIN backers
WHERE BackerID NOT 
IN (

SELECT BackerID
FROM _AllShippingAddresses
)
"); ?>

	</body>
</html>
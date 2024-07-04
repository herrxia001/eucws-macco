<?php

class myResource{	
		public $sysMsgLoading			="Loading data ...";
		public $sysMsgNoRecord			="No record found";
		public $msgErrLoadDb			="Load data error";
		public $msgErrNoData			="No data found";
		public $msgErrDataInput			="Invalid data";
		public $msgErrNoEnoughStock		="No enough stock";
		public $msgErrDupProduct		="Product exists";
		public $msgErrDupID				="ID exists";
		public $msgErrDupData			="Data exists";
		public $msgErrProductNoExist	="Product not found";
		public $msgConfirmCancelOrder	="Do you really want to cancel this order?";
		public $msgConfirmSave			="Do you want to save?";
		public $msgConfirmPrint			="Do you want to print?";
		public $msgConfirmDelete		="Do you really want to delete the data?";
		public $msgConfirmEdit			="Do you want to edit data?";
		public $msgConfirmQuit			="Data not saved. Do you really want to quit?";
		public $msgErrDatabase			="System error. Please try later.";
		public $msgErrNoRecord			="No data";
		public $msgErrNoCustomer		="Please enter customer data.";
		public $msgDataNotComplete		="Data not completed. Please save or print, or delete.";
		public $msgDataSaved			="Data saved";
		public $msgInvoiceOk			="Invoice done";
		public $msgInvoiceErr			="Invoice error";
		public $msgInvoiceDup			="Invoice exists";
		public $msgNoCustOrder			="No order found";
		
		public $txtUnitOne		="";
		public $txtUnitMore		="";
		
		// common
		public $comAdd			= "Add";
		public $comAddress		= "Address";
		public $comAll			= "Select All";
		public $comBarcode 		= "Barcode";
		public $comBarcodeScanInfo = "PLEASE SCAN BARCODE";
		public $comBarcodeYes 	= "BARCODE OK";
		public $comBarcodeNo	= "BARCODE ERROR";
		public $comCancel 		= "Cancel";
		public $comCity			= "City";
		public $comColor 		= "Color";
		public $comCompany 		= "Company";
		public $comCompProfile 	= "Company";
		public $comCompCap		= array("Name","Address","Post","City","Country","Tel","Fax","Mobile","E-Mail","WhatsApp","Tax ID","UID Nr.","IBAN","BIC","Tax","Website");
		public $comContact		= "Contact";
		public $comCost			= "Cost";
		public $comCountry		= "Country";
		public $comCustomer 	= "Customer";
		public $comCustomers 	= "Customers";
		public $comCustomerAll 	= "All Customers";
		public $comCustomerUnknown 	= "Unknown";
		public $comCustomerNew 	= "New Customer";
		public $comCustomerSearch 	= "Search Customer";
		public $comCustomerProfile 	= "Profile";
		public $comCustomerOther 	= "Other Info";
		public $comDelete	 	= "Delete";
		public $comDiscount	 	= "Discount";
		public $comDiscountRate	 	= "Rate";
		public $comDiscountValue	 = "Value";
		public $comDue		 	= "Due";
		public $comEdit		= "Edit";
		public $comEMail		= "E-Mail";
		public $comExport		= "Export";
		public $comFee		 	= "Fees";
		public $comFeeShipping	= "Shipping";
		public $comFeeNachnahme	= "Nachnahme";
		public $comFeeBank		= "Banking";
		public $comFeeOther		= "Other";
		public $comFileExport	= "File Export";
		public $comHome			= "Home";
		public $comID			= "ID";
		public $comInventory 	= "Inventory";
		public $comInvoice 		= "Invoice";
		public $comInvoiceNo 	= "Invoice No.";
		public $comItem 		= "Item";
		public $comListRefund 	= "Refund List";
		public $comLogout		= "Logout";		
		public $comManagement	 = "Settings";
		public $comName			= "Name";
		public $comName1		= "Other Name";
		public $comOK		 	= "OK";
		public $comOptions	 	= "Options";
		public $comOrder		= "Orders";
		public $comOrders		= "Orders";
		public $comOrderNew		= "Order";
		public $comOrderNo		= "Order No.";
		public $comPackages 	= "Packages";
		public $comPackageCap 	= "Pieces/Pack";
		public $comPaid		 	= "Paid";
		public $comPassword		 = "Password";
		public $comPassMgt		 = "Password";
		public $comPassNew		 = "New Password";	
		public $comPassRe		 = "Re-Enter";
		public $comPayment	 	= "Payment";
		public $comPaymentArt	 = "Method";
		public $comPaymentValue	 = "Value";
		public $comPosition		= "Position";
		public $comPost			= "Post";
		public $comPrice	 	= "Price";
		public $comPrint	 	= "Print";
		public $comProduct	 	= "Product";
		public $comProductList	 = "Products";
		public $comProductName 	= "Name";
		public $comProductNo 	= "Art. No.";
		public $comProductNew 	= "Create New";
		public $comProductSearch 	= "Please enter ART No.";
		public $comProfit	 	= "Profit";
		public $comProfitRate	 	= "Rate";
		public $comPurchase		= "Purchase";
		public $comPurchaseNo	= "No.";
		public $comPurchaseNew	= "Purchase";
		public $comPurchaseForm	= "Purchase";
		public $comQuantity 	= "Quantity";
		public $comRefund 		= "Refund";		
		public $comRefundNo 	= "Refund No.";
		public $comRefundTime 	= "Time Refund";
		public $comRemark	 	= "Remark";
		public $comReport	 	= "Reports";
		public $comSave		 	= "Save";
		public $comSelect	 	= "Select";
		public $comSettings	 	= "Options";
		public $comSort			= 'Sort';
		public $comSortUpdated	= 'Newest Updated';
		public $comSortCreated	= 'Newest Created';
		public $comSortCodeAZ	= 'A-Z';
		public $comSortCodeZA	= 'Z-A';
		public $comSortCountDesc= 'Pieces More';
		public $comSortCountAsc	= 'Pieces Less';
		public $comSortValueDesc	= 'Value More';
		public $comSortValueAsc	= 'Value Less';
		public $comSortSaleValueDesc= 'Sale Value More';
		public $comSortSaleCountDesc= 'Sale Pieces More';
		public $comSortProfitDesc= 'Profit More';
		public $comSortPurchaseDate= 'New Stock';
		public $comSortInvDesc= 'Stock More';
		public $comSortInvAsc= 'Stock Less';
		public $comSortInValueDesc= 'Purchase Pieces More';
		public $comSortInCountDesc= 'Purchase Value More';
		public $comSubtotal 	= "Subtotal";
		public $comSupplier		= "Supplier";
		public $comSuppliers	= "Suppliers";
		public $comSupplierAll	= "All Suppliers";
		public $comSupplierUnknown	= "Unknown";
		public $comTax		 	= "Tax";
		public $comTaxNo		 = "Tax ID";
		public $comTaxRate		 = "Tax Rate";
		public $comTaxValue		 = "Tax Value";
		public $comTel			= "Tel";
		public $comTime			 = "Time";
		public $comTimeEdit		 = "Edit Time";
		public $comType			 = "Type";
        public $comSeason		 = "Categorisation";
		public $comTypes		 = "Types";
		public $comTypeAdd		 = "Add Type";
		public $comTypeEdit		 = "Edit Type";
		public $comTypeAll		 = "All Types";
        public $comSeasonAll		 = "All Categorisation";
		public $comTypeUnkown	 = "Unknown Types";
		public $comTotal	 	= "Total";
		public $comTotalGross 	= "Gross";
		public $comTotalNet 	= "Net";
		public $comTotalPrice 	= "Sales";
		public $comTotalQuantity = "Quantity";
		public $comTotalRecord 	= "Records";
		public $comUnit 		= "Unit";
		public $comUser 		= "User";
		public $comUserAll 		= "All Users";
		public $comValue 		= "Value";
		public $comVariant	 	= "Variant";
    public $comVariantSize	 	= "size";
		public $comVariants 	= "Variants";
		public $comVariantNew 	= "New Variant";
		public $comVat 			= "VAT";
		public $comWhatsApp		= "WhatsApp";
		public $comWeChat		= "WeChat";
		
		// options
		public $opDecimalNormal			= "Use dot as decimal";
		public $opDecimalComma			= "Use comma as decimal";
		public $opPrintNoProductName	= "Do not print product name";
		public $opPrintNoART	 		= "Do not print prefix 'ART'";
		public $opPrintReklamation	 	= "Print Reklamation";
		public $opPrintQRCode	 		= "Print Banking QR-Code ";
		public $opPrintReklamation1	 	= "Print Payment Reklamation";
		public $opWildSearch	 		= "Use wild search";
		public $opPurPosition	 		= "Add position to purchase item";
		
		// modalSelTime
		public $mdstTitle 			= "Time Selection";
		public $mdstRdAll 			= "All";
		public $mdstRdToday 		= "Today";
		public $mdstRdYesterday 	= "Yesterday";
		public $mdstRdThisMonth 	= "This Month";
		public $mdstRdLastMonth 	= "Last Month";
		public $mdstRdThisYear 		= "This Year";
		public $mdstRdLastYear 		= "Last Year";
		public $mdstYear 			= "Year";
		public $mdstMonth 			= "Month";
		public $mdstFrom 			= "From";
		public $mdstTo 				= "To";
		
// NEW		
public $comPayCash			= "Cash";
public $comPayCard			= "Card";
public $comPayTransfer		= "Transfer";
public $comPayCheck			= "Check";
public $comPayPayPal		= "PayPal";
public $comPayPrepaid		= "In Advance";
public $comPayOther			= "Other";

// Report
public $rptReport					= "Sales Report";
public $rptProProductTotal			= "Products";
public $rptProCountTotal			= "Quantity";
public $rptProValueTotal			= "Value";
public $rptProProfitRate			= "Profit";
public $rptProProduct				= "Product";
public $rptProInventory				= "In Stock";
public $rptProSales					= "Sales";
public $rptProValue					= "Value";
public $rptProProfit				= "Profit";
public $rptCustTotal				= "Customers";
public $rptCustProductList			= "Products";
public $rptCustOrderList			= "Orders";
public $rptSupInCount				= "Purchase";
public $rptSupInValue				= "Value";
public $rptSupOutCount				= "Sales";
public $rptSupOutValue				= "Value";

// INVOICE
public $anMsgNoNewInvoice				= "No new invoice";

// cust_search.php
public $fmCustSrchTitle		= 'Search Customer';
public $fmCustSrchMsgChoose	= 'Please select one customer';
public $fmCustSrchMsgNoFound= 'No customer found';
public $fmCustSrchCapId		= 'ID';
public $fmCustSrchCapName	= 'Name';
public $fmCustSrchCapName1	= 'Name1';
public $fmCustSrchCapPost	= 'Post';
public $fmCustSrchCapUst	= 'Ust-IdNr.';	
public $fmCustSrchCapCity	= 'City';
public $fmCustSrchBtnNext	= 'Next';
public $fmCustSrchBtnNew	= 'New';
}


$seasonArr = Array();
$seasonArr[1] = "spring clothes";
$seasonArr[2] = "Summer clothes";
$seasonArr[3] = "Autumn clothes";
$seasonArr[4] = "Winter clothes";


$sizeArr = Array();
$sizeArr[] = "S";
$sizeArr[] = "M";
$sizeArr[] = "L";
$sizeArr[] = "XL";
$sizeArr[] = "XXL";
?>
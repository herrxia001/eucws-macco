<?php

class myResource{
		public $sysMsgLoading			="数据载入中 ...";
		public $sysMsgNoRecord			="没有符合条件的数据";
		public $msgErrLoadDb			="读取数据有误";
		public $msgErrNoData			="没有数据";
		public $msgErrDataInput			="数据有误";
		public $msgErrNoEnoughStock		="库存不足";
		public $msgErrDupProduct		="数据重复";
		public $msgErrDupID				="号码重复";
		public $msgErrDupData			="数据已存在";
		public $msgErrProductNoExist	="数据不存在";
		public $msgConfirmCancelOrder	="确认取消订单?";
		public $msgConfirmSave			="确认保存?";
		public $msgConfirmPrint			="确认打印?";
		public $msgConfirmDelete		="确认删除?";
		public $msgConfirmEdit			="确认修改?";
		public $msgConfirmQuit			="数据尚未保存，确定退出?";
		public $msgErrDatabase			="系统有误. 请稍后再试";
		public $msgErrNoRecord			="没有数据";
		public $msgErrNoCustomer		="请输入客户数据";
		public $msgDataNotComplete		="没有完成.请保存/打印, 或删除";
		public $msgDataSaved			="数据保存成功";
		public $msgInvoiceOk			="发票成功";
		public $msgInvoiceErr			="发票错误";
		public $msgInvoiceDup			="发票已存在，不能重复";
		public $msgNoCustOrder			="没有找到该客户在所选时间的销售记录";
		
		public $txtUnitOne		="";
		public $txtUnitMore		="";
		
		// common
		public $comAdd			= "新增";
		public $comAddress		= "地址";
		public $comAddress1		= "地址1";
		public $comAll			= "选择全部";
		public $comBack 		= "返回";
		public $comBarcode 		= "条码";
		public $comBarcodeScanInfo = "请扫描条码";
		public $comBarcodeYes 	= "条码有效";
		public $comBarcodeNo	= "条码错误";
		public $comCancel 		= "取消";
		public $comCity			= "城市";
		public $comColor 		= "颜色";
		public $comCompany 		= "公司";
		public $comCompProfile 	= "公司资料";
		public $comCompCap			= array("名称","地址","邮编","城市","国家","电话","传真","手机","E-Mail","WhatsApp","Steuer Nr.","USt-IdNr.","IBAN","BIC","税率","网址","法院登记号","法人");			
		public $comContact		= "联系人";
		public $comCost			= "成本";
		public $comCountry		= "国家";
		public $comCustomer 	= "客户";
		public $comCustomers 	= "客户管理";
		public $comCustomerAll 	= "所有客户";
		public $comCustomerUnknown 	= "未知客户";
		public $comCustomerNew 	= "创建新客户";	
		public $comCustomerSearch 	= "查找客户";
		public $comCustomerProfile 	= "基本资料";
		public $comCustomerOther 	= "其他信息";
		public $comDelete	 	= "删除";
		public $comDiscount	 	= "折扣";
		public $comDiscountRate	 	= "折扣率";
		public $comDiscountValue	 = "金额";
		public $comDue		 	= "未付";
		public $comEdit		= "编辑";
		public $comEMail		= "E-Mail";
		public $comExport		= "数据导出";
		public $comFee		 	= "费用";
		public $comFeeShipping	= "运费";
		public $comFeeNachnahme	= "Nachnahme";
		public $comFeePack		= "包装费用";
		public $comFeeBank		= "银行费用";
		public $comFeeOther		= "其他费用";
		public $comFileExport	= "导出文件";
		public $comHome			= "首页";
		public $comID			= "编号";
		public $comInventory 	= "库存";
		public $comInvoice 		= "发票";
		public $comInvoiceNo 	= "发票号";
		public $comItem 		= "项目";
		public $comListRefund 	= "退货单列表";	
		public $comLogout		= "退出";	
		public $comManagement	 = "管理";	
		public $comName			= "名称";
		public $comName1		= "其他名称";
		public $comOK		 	= "确定";
		public $comOptions	 	= "选项";
		public $comOrder		= "销售";
		public $comOrders		= "订单列表";
		public $comOrderNew		= "新的订单";
		public $comOrderNo		= "订单号";
		public $comPackages 	= "包装配比";
		public $comPackageCap 	= "每包件数";
		public $comPaid		 	= "已付";
		public $comPassword		 = "密码";
		public $comPassMgt		 = "密码管理";
		public $comPassNew		 = "新的密码";	
		public $comPassRe		 = "再次输入";		
		public $comPayment	 	= "付款";
		public $comPaymentArt	 = "付款方式";
		public $comPaymentValue	 = "金额";
		public $comPosition		= "位置";
		public $comPost			= "邮编";
		public $comPrice	 	= "售价";
		public $comPrint	 	= "打印";
		public $comProduct	 	= "货品";
		public $comProductList	 = "库存";
		public $comProductName 	= "名称";
		public $comProductNo 	= "货号";
		public $comProductNew 	= "创建新商品";
		public $comProductSearch 	= "输入货号查找商品";
		public $comProfit	 	= "利润";
		public $comProfitRate	 	= "利润率";
		public $comPurchase		= "进货";
		public $comPurchaseNo	= "单号";
		public $comPurchaseNew	= "新的进货";
		public $comPurchaseForm	= "进货单";
		public $comQuantity 	= "件数";
		public $comRefund 		= "退货";			
		public $comRefundNo 	= "退货单号";
		public $comRefundTime 	= "退货时间";
		public $comRemark	 	= "备注";
		public $comReport	 	= "统计";
		public $comSave		 	= "保存";
		public $comSelect	 	= "选择";
		public $comSettings	 	= "设置";
		public $comSort			= '排序';
		public $comSortUpdated	= '最新更新';
		public $comSortCreated	= '最新创建';
		public $comSortCodeAZ	= 'A-Z';
		public $comSortCodeZA	= 'Z-A';
		public $comSortCountDesc= '件数最多';
		public $comSortCountAsc	= '件数最少';
		public $comSortValueDesc= '金额最多';
		public $comSortValueAsc	= '金额最少';
		public $comSortSaleValueDesc= '销售金额最大';
		public $comSortSaleCountDesc= '销售件数最多';
		public $comSortProfitDesc= '利润率最大';
		public $comSortPurchaseDate= '最新进货';
		public $comSortInvDesc= '库存最多';
		public $comSortInvAsc= '库存最少';
		public $comSortInValueDesc= '进货金额最大';
		public $comSortInCountDesc= '进货件数最多';
		public $comSubtotal 	= "小计";
		public $comSupplier		= "厂家";
		public $comSuppliers	= "厂家管理";
		public $comSupplierAll	= "全部厂家";
		public $comSupplierUnknown	= "未知厂家";
		public $comTax		 	= "税";
		public $comTaxNo		 = "所得税号";
		public $comTaxRate		 = "税率";
		public $comTaxValue		 = "税款";
		public $comTel			= "电话";
		public $comTime			 = "时间";
		public $comTimeEdit		 = "时间设置";
		public $comType			 = "类别";
        public $comSeason		 = "分类";
		public $comTypes		 = "商品类别";
		public $comTypeAdd		 = "创建类别";
		public $comTypeEdit		 = "编辑类别";
		public $comTypeAll		 = "全部类别";
        public $comSeasonAll	 = "全部分类";
		public $comTypeUnkown	 = "未知类别";
		public $comTotal	 	= "总计";
		public $comTotalGross 	= "总计";
		public $comTotalNet 	= "应付金额";
		public $comTotalPrice 	= "销售额";
		public $comTotalQuantity = "件数";
		public $comTotalRecord 	= "单数";
		public $comUnit 		= "单位";
		public $comUser 		= "用户";
		public $comUserAll 		= "所有用户";
		public $comValue 		= "金额";
		public $comVariant	 	= "款色";
        public $comVariantSize	 	= "大小";
		public $comVariants 	= "款色管理";
		public $comVariantNew 	= "新的款色";
		public $comVat 			= "欧盟税号";
		public $comWhatsApp		= "WhatsApp";
		public $comWeChat		= "微信";
		
		// options
		public $opDecimalNormal			= "小数点用点号";
		public $opDecimalComma			= "小数点用逗号";
		public $opPrintNoProductName	= "不打印货品名称";		
		public $opPrintNoART	 		= "货号前不打印'ART'前缀";
		public $opPrintReklamation	 	= "打印Reklamation";
		public $opPrintQRCode	 		= "打印付款二维码 ";
		public $opPrintReklamation1	 	= "打印付款声明";
		public $opWildSearch	 		= "使用模糊搜索";
		public $opPurPosition	 		= "在进货项目中添加库存位置";
			
		// modalSelTime
		public $mdstTitle 			= "选择时间";
		public $mdstRdAll 			= "所有时间";
		public $mdstRdToday 		= "今天";
		public $mdstRdYesterday 	= "昨天";
		public $mdstRdThisMonth 	= "本月";
		public $mdstRdLastMonth 	= "上月";
		public $mdstRdThisYear 		= "今年";
		public $mdstRdLastYear 		= "去年";
		public $mdstYear 			= "年";
		public $mdstMonth 			= "月";
		public $mdstFrom 			= "起";
		public $mdstTo 				= "至";

// NEW
public $comPayCash			= "现金";
public $comPayCard			= "刷卡";
public $comPayTransfer		= "银行转账";
public $comPayCheck			= "支票";
public $comPayPayPal		= "PayPal";
public $comPayPrepaid		= "预付款";
public $comPayOther			= "货到付款";

public $comTransFee			= "运费";
public $comTransNote		= "备注";
public $comTransporter		= "运输公司";
public $comTransNo			= "运单号";

public $comPriceSelection	= "选择价格";
public $comPriceHistory		= "历史价格";
public $comPriceSystem		= "预设价格";

public $comStatus			= "状态";
public $comStatusNormal		= "正常";
public $comStatusOffline	= "下架";

public $comOrderMerge		= "订单合并";
public $comMergePreview		= "合并预览";
public $comMerge			= "合并";
public $msgMergeConfirm		= "确认合并?";
public $msgMergeOK			= "订单合并成功";
public $msgMergeError		= "订单合并错误, 请稍后再试";

public $comBatchDiscount	= "批量打折";

// APP
public $comAppMgt			= "APP商品";
public $comAppTypes			= "商品系列";
public $comAppTypeAdd		= "创建商品系列";
public $comAppTypeUpdate	= "编辑商品系列";
public $comAppUsers			= "用户审批";
public $comAppReport		= "APP统计";
public $comAppConfirm		= "确认";
public $appRefresh			= "更新";
public $appAll				= "全部";
public $appStatusNormal		= "正常";
public $appStatusOffline	= "下架";
public $appStatusRestock	= "补货";
public $appOldPrice			= "原价";
public $appStatus			= "状态";
public $appHot				= "热卖";
public $appNew				= "新品";
public $appDiscount			= "折扣";
public $appTag				= "标签";
public $appNote				= "描述";
public $appUsersTypeComp	= "公司";
public $appUsersTypePer		= "个人";
public $appUsersPending		= "待审批";
public $appUsersApproved	= "通过";
public $appUsersRejected	= "未通过";
public $appUsersApprove		= "批准";
public $appUsersReject		= "拒绝";
public $appUsersConfirmReject	= "确认拒绝";
public $appUserViewMessage		= "查看原因";
public $appUserViewFile		= "查看文件";
public $appUserNoFile		= "没有文件";
public $appUserMessage		= "拒绝原因";
public $appMsgUserReject	= "确认拒绝该客户的申请?";
public $appMsgUserApprove	= "确认批准该客户的申请?";
public $appZeroSale			= "库存为零时继续销售";
public $appRptSales			= "销售";
public $appRptUsers			= "客户";
public $appRptProducts		= "商品";
public $appRptDate			= "日期";
public $appRptValue			= "销售额";
public $appRptValueSum		= "总销售额";
public $appRptUserApply		= "客户申请";
public $appRptUserSum		= "客户申请总数";

// Report
public $rptReport					= "销售统计";
public $rptProProductTotal			= "销售商品总数";
public $rptProCountTotal			= "总件数";
public $rptProValueTotal			= "总金额";
public $rptProProfitRate			= "利润率";
public $rptProProduct				= "商品";
public $rptProInventory				= "库存";
public $rptProSales					= "售出";
public $rptProValue					= "金额";
public $rptProProfit				= "利润";
public $rptCustTotal				= "客户数";
public $rptCustProductList			= "商品列表";
public $rptCustOrderList			= "订单列表";
public $rptSupInCount				= "进货总数";
public $rptSupInValue				= "进货金额";
public $rptSupOutCount				= "销售总数";
public $rptSupOutValue				= "销售金额";

// INVOICE
public $anMsgNoNewInvoice				= "没有待处理的发票";

public $comNoch = "还剩";
public $comTag = "天";
// cust_search.php
public $fmCustSrchTitle		= '选择客户';
public $fmCustSrchMsgChoose	= '请选择一个客户';
public $fmCustSrchMsgNoFound	= '没有找到符合条件的客户';
public $fmCustSrchCapId		= '编号';
public $fmCustSrchCapName	= '名称';
public $fmCustSrchCapName1	= '别名';
public $fmCustSrchCapPost	= '邮编';
public $fmCustSrchCapUst		= 'Ust-IdNr.';	
public $fmCustSrchCapAddr	= '地址';
public $fmCustSrchBtnNext	= '下一步';
public $fmCustSrchBtnNew		= '创建新的客户';



}


$seasonArr = Array();
$seasonArr[1] = "春装";
$seasonArr[2] = "夏装";
$seasonArr[3] = "秋装";
$seasonArr[4] = "冬装";


$sizeArr = Array();
$sizeArr[] = "S";
$sizeArr[] = "M";
$sizeArr[] = "L";
$sizeArr[] = "XL";
$sizeArr[] = "XXL";
?>
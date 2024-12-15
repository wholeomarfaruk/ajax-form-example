<?php
//All Tables list
enum Table:string {

    case users="user";
    case adminUser="admin_user";
    case loginSession="login_session";
    case site="site_settings";
    case TrxnHistory="trxn_history";
    case UserBalance = "user_balances";
    case DepositMethods = "deposit_methods";
    case WithdrawalMethods = "withdrawal_methods";
    case TradeInvestHistory = "trade_invest_history";
    case Campaign = "campaigns";
    case CampaignPerticipents = "campaign_participants";
    case TradSlab = "trade_slab";
    case ReferralTrxn = "referral_trxn";
    case TempEmailDomains= "temporary_email_domains";
    case Post = "post";
    case Notifications = "notifications";
  }
  //user and admin role declared
  enum Role:string {
      case admin="admin";
      case user="user";
  }

  //status code 
  enum Status:int {
      case Inactive=0;
      case Active=1;
      case Blocked=2;
  }
  enum Gender:int{
    case NotSet=0;
    case Male=1;
    case Female=2;
    case Others=3;
  }
  enum TrxnStatus: int {
    case Rejected = 0;
    case Completed = 1;
    case Pending = 2;
    case Processing = 3;
    case ManualCheck = 4;
    case Failed = 5;
    case Reversed = 6;
    case Refunded = 7;
    case OnHold = 8;

}

  enum TrxnType: string {
    case Deposit = 'deposit';
    case Withdrawal = 'withdrawal';
    case Adjustment = 'adjustment';
    case Profit = 'profit';
    case Refund = 'refund';
    case Chargeback = 'chargeback';
    case Bonus = 'bonus';
    case Transfer = 'transfer';
    case Fee = 'fee';
    case ReferralComm = 'referral_commission';
}


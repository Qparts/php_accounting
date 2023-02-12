<?php
    use App\Models\Utility;
      //  $logo=asset(Storage::url('uploads/logo/'));
        $logo=\App\Models\Utility::get_file('uploads/logo/');
        $company_logo=Utility::getValByName('company_logo_dark');
        $company_logos=Utility::getValByName('company_logo_light');
        $company_small_logo=Utility::getValByName('company_small_logo');
        $setting = \App\Models\Utility::colorset();
        $mode_setting = \App\Models\Utility::mode_layout();
        $emailTemplate     = \App\Models\EmailTemplate::first();
        $lang= Auth::user()->lang;


?>

<?php if(isset($setting['cust_theme_bg']) && $setting['cust_theme_bg'] == 'on'): ?>
    <nav class="dash-sidebar light-sidebar transprent-bg">
<?php else: ?>
    <nav class="dash-sidebar light-sidebar">
<?php endif; ?>
    <div class="navbar-wrapper">
        <div class="m-header main-logo">
            <a href="#" class="b-brand">


                <?php if($mode_setting['cust_darklayout'] && $mode_setting['cust_darklayout'] == 'on' ): ?>
                    <img src="<?php echo e($logo . '/' . (isset($company_logos) && !empty($company_logos) ? $company_logos : 'logo-dark.png')); ?>"
                         alt="<?php echo e(config('app.name', 'ERPGo-SaaS')); ?>" class="logo logo-lg">
                <?php else: ?>
                    <img src="<?php echo e($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png')); ?>"
                         alt="<?php echo e(config('app.name', 'ERPGo-SaaS')); ?>" class="logo logo-lg">
                <?php endif; ?>

            </a>
        </div>
        <div class="navbar-content">
            <?php if(\Auth::user()->type != 'client'): ?>
                <ul class="dash-navbar">
                    <!--------------------- Start Dashboard ----------------------------------->
                    <?php if( Gate::check('show hrm dashboard') || Gate::check('show project dashboard') || Gate::check('show account dashboard')): ?>
                        <li class="dash-item dash-hasmenu
                                <?php echo e(( Request::segment(1) == null ||Request::segment(1) == 'account-dashboard' || Request::segment(1) == 'income report'
                                   || Request::segment(1) == 'report' || Request::segment(1) == 'reports-payroll' || Request::segment(1) == 'reports-leave' ||
                                    Request::segment(1) == 'reports-monthly-attendance') ?'active dash-trigger':''); ?>">

                            <a href="#!" class="dash-link "><span class="dash-micon"><i class="ti ti-home"></i></span><span class="dash-mtext"><?php echo e(__('Dashboard')); ?></span>
                                <span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>

                            <ul class="dash-submenu">
                                <?php if(\Auth::user()->show_account() == 1 && Gate::check('show account dashboard')): ?>
                                    <li class="dash-item dash-hasmenu <?php echo e(( Request::segment(1) == null   || Request::segment(1) == 'account-dashboard'|| Request::segment(1) == 'report') ? ' active dash-trigger' : ''); ?>">
                                        <a class="dash-link" href="#"><?php echo e(__('Accounting ')); ?><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                        <ul class="dash-submenu">
                                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('show account dashboard')): ?>
                                        <li class="dash-item <?php echo e(( Request::segment(1) == null || Request::segment(1) == 'account-dashboard') ? ' active' : ''); ?>">
                                            <a class="dash-link" href="<?php echo e(route('dashboard')); ?>"><?php echo e(__(' Overview')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                            <?php if( Gate::check('income report') || Gate::check('expense report') || Gate::check('income vs expense report') ||
                                                 Gate::check('tax report')  || Gate::check('loss & profit report') || Gate::check('invoice report') ||
                                                 Gate::check('bill report') || Gate::check('stock report') || Gate::check('invoice report') ||
                                                 Gate::check('manage transaction')||  Gate::check('statement report')): ?>

                                                <li class="dash-item dash-hasmenu
                                                              <?php echo e((Request::segment(1) == 'report')? 'active dash-trigger ' :''); ?>">

                                                        <a class="dash-link" href="#"><?php echo e(__('Reports')); ?><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                                        <ul class="dash-submenu">
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('expense report')): ?>
                                                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'report.expense.summary' ) ? ' active' : ''); ?>">
                                                                        <a class="dash-link" href="<?php echo e(route('report.expense.summary')); ?>"><?php echo e(__('Expense Summary')); ?></a>
                                                                    </li>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('income vs expense report')): ?>
                                                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'report.income.vs.expense.summary' ) ? ' active' : ''); ?>">
                                                                        <a class="dash-link" href="<?php echo e(route('report.income.vs.expense.summary')); ?>"><?php echo e(__('Income VS Expense')); ?></a>
                                                                    </li>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('statement report')): ?>
                                                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'report.account.statement') ? ' active' : ''); ?>">
                                                                        <a class="dash-link" href="<?php echo e(route('report.account.statement')); ?>"><?php echo e(__('Account Statement')); ?></a>
                                                                    </li>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('invoice report')): ?>
                                                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'report.invoice.summary' ) ? ' active' : ''); ?>">
                                                                        <a class="dash-link" href="<?php echo e(route('report.invoice.summary')); ?>"><?php echo e(__('Invoice Summary')); ?></a>
                                                                    </li>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('bill report')): ?>
                                                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'report.bill.summary' ) ? ' active' : ''); ?>">
                                                                        <a class="dash-link" href="<?php echo e(route('report.bill.summary')); ?>"><?php echo e(__('Bill Summary')); ?></a>
                                                                    </li>
                                                                <?php endif; ?>
                                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stock report')): ?>
                                                                        <li class="dash-item <?php echo e((Request::route()->getName() == 'report.product.stock.report' ) ? ' active' : ''); ?>">
                                                                            <a href="<?php echo e(route('report.product.stock.report')); ?>" class="dash-link"><?php echo e(__('Product Stock')); ?></a>
                                                                        </li>
                                                                    <?php endif; ?>

                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('loss & profit report')): ?>
                                                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'report.profit.loss.summary' ) ? ' active' : ''); ?>">
                                                                        <a class="dash-link" href="<?php echo e(route('report.profit.loss.summary')); ?>"><?php echo e(__('Profit & Loss')); ?></a>
                                                                    </li>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage transaction')): ?>
                                                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'transaction.index' || Request::route()->getName() == 'transfer.create' || Request::route()->getName() == 'transaction.edit') ? ' active' : ''); ?>">
                                                                        <a class="dash-link" href="<?php echo e(route('transaction.index')); ?>"><?php echo e(__('Transaction')); ?></a>
                                                                    </li>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('income report')): ?>
                                                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'report.income.summary' ) ? ' active' : ''); ?>">
                                                                        <a class="dash-link" href="<?php echo e(route('report.income.summary')); ?>"><?php echo e(__('Income Summary')); ?></a>
                                                                    </li>
                                                                <?php endif; ?>
                                                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('tax report')): ?>
                                                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'report.tax.summary' ) ? ' active' : ''); ?>">
                                                                        <a class="dash-link" href="<?php echo e(route('report.tax.summary')); ?>"><?php echo e(__('Tax Summary')); ?></a>
                                                                    </li>
                                                                <?php endif; ?>
                                                            </ul>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </li>
                                <?php endif; ?>













































                            </ul>
                        </li>
                    <?php endif; ?>
                    <!--------------------- End Dashboard ----------------------------------->


                    <!--------------------- Start HRM ----------------------------------->









































































































































































































































































                    <!--------------------- End HRM ----------------------------------->

                    <!--------------------- Start Account ----------------------------------->

                    <?php if(\Auth::user()->show_account() == 1): ?>
                    <?php if( Gate::check('manage customer') || Gate::check('manage vender') || Gate::check('manage customer') || Gate::check('manage vender') ||
                         Gate::check('manage proposal') ||  Gate::check('manage bank account') ||  Gate::check('manage bank transfer') ||  Gate::check('manage invoice')
                         ||  Gate::check('manage revenue') ||  Gate::check('manage credit note') ||  Gate::check('manage bill')  ||  Gate::check('manage payment') ||
                          Gate::check('manage debit note') || Gate::check('manage chart of account') ||  Gate::check('manage journal entry') ||   Gate::check('balance sheet report')
                          || Gate::check('ledger report') ||  Gate::check('trial balance report')  ): ?>
                            <li class="dash-item dash-hasmenu
                                        <?php echo e((Request::route()->getName() == 'print-setting' || Request::segment(1) == 'customer' ||
                                            Request::segment(1) == 'vender' || Request::segment(1) == 'proposal' || Request::segment(1) == 'bank-account' ||
                                            Request::segment(1) == 'bank-transfer' || Request::segment(1) == 'invoice' || Request::segment(1) == 'revenue' ||
                                            Request::segment(1) == 'credit-note' || Request::segment(1) == 'taxes' || Request::segment(1) == 'product-category' ||
                                            Request::segment(1) == 'product-unit' || Request::segment(1) == 'payment-method' || Request::segment(1) == 'custom-field' ||
                                            Request::segment(1) == 'chart-of-account-type' || ( Request::segment(1) == 'transaction') &&  Request::segment(2) != 'ledger'
                                            &&  Request::segment(2) != 'balance-sheet' &&  Request::segment(2) != 'trial-balance' || Request::segment(1) == 'goal'
                                            || Request::segment(1) == 'budget'|| Request::segment(1) == 'chart-of-account' || Request::segment(1) == 'journal-entry' ||
                                             Request::segment(2) == 'ledger' ||  Request::segment(2) == 'balance-sheet' ||  Request::segment(2) == 'trial-balance' ||
                                             Request::segment(1) == 'bill' || Request::segment(1) == 'payment' || Request::segment(1) == 'debit-note')?' active dash-trigger':''); ?>">
                                        <a href="#!" class="dash-link"><span class="dash-micon"><i class="ti ti-box"></i></span><span class="dash-mtext"><?php echo e(__('Accounting System ')); ?>

                                            </span><span class="dash-arrow"><i data-feather="chevron-right"></i></span>
                                        </a>
                                    <ul class="dash-submenu">
                                    <?php if(Gate::check('manage customer')): ?>
                                        <li class="dash-item <?php echo e((Request::segment(1) == 'customer')?'active':''); ?>">
                                            <a class="dash-link" href="<?php echo e(route('customer.index')); ?>"><?php echo e(__('Customer')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if(Gate::check('manage vender')): ?>
                                        <li class="dash-item <?php echo e((Request::segment(1) == 'vender')?'active':''); ?>">
                                            <a class="dash-link" href="<?php echo e(route('vender.index')); ?>"><?php echo e(__('Vendor')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if(Gate::check('manage proposal')): ?>
                                        <li class="dash-item <?php echo e((Request::segment(1) == 'proposal')?'active':''); ?>">
                                            <a class="dash-link" href="<?php echo e(route('proposal.index')); ?>"><?php echo e(__('Proposal')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if( Gate::check('manage bank account') ||  Gate::check('manage bank transfer')): ?>
                                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'bank-account' || Request::segment(1) == 'bank-transfer')? 'active dash-trigger' :''); ?>">
                                            <a class="dash-link" href="#"><?php echo e(__('Banking')); ?><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                            <ul class="dash-submenu">
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'bank-account.index' || Request::route()->getName() == 'bank-account.create' || Request::route()->getName() == 'bank-account.edit') ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('bank-account.index')); ?>"><?php echo e(__('Account')); ?></a>
                                                </li>
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'bank-transfer.index' || Request::route()->getName() == 'bank-transfer.create' || Request::route()->getName() == 'bank-transfer.edit') ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('bank-transfer.index')); ?>"><?php echo e(__('Transfer')); ?></a>
                                                </li>
                                            </ul>
                                        </li>
                                    <?php endif; ?>
                                    <?php if( Gate::check('manage invoice') ||  Gate::check('manage revenue') ||  Gate::check('manage credit note')): ?>
                                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'invoice' || Request::segment(1) == 'revenue' || Request::segment(1) == 'credit-note')? 'active dash-trigger' :''); ?>">
                                            <a class="dash-link" href="#"><?php echo e(__('Income')); ?><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                            <ul class="dash-submenu">
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'invoice.index' || Request::route()->getName() == 'invoice.create' || Request::route()->getName() == 'invoice.edit' || Request::route()->getName() == 'invoice.show') ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('invoice.index')); ?>"><?php echo e(__('Invoice')); ?></a>
                                                </li>
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'revenue.index' || Request::route()->getName() == 'revenue.create' || Request::route()->getName() == 'revenue.edit') ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('revenue.index')); ?>"><?php echo e(__('Revenue')); ?></a>
                                                </li>
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'credit.note' ) ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('credit.note')); ?>"><?php echo e(__('Credit Note')); ?></a>
                                                </li>
                                            </ul>
                                        </li>
                                    <?php endif; ?>
                                    <?php if( Gate::check('manage bill')  ||  Gate::check('manage payment') ||  Gate::check('manage debit note')): ?>
                                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'bill' || Request::segment(1) == 'payment' || Request::segment(1) == 'debit-note')? 'active dash-trigger' :''); ?>">
                                            <a class="dash-link" href="#"><?php echo e(__('Expense')); ?><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                            <ul class="dash-submenu">
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'bill.index' || Request::route()->getName() == 'bill.create' || Request::route()->getName() == 'bill.edit' || Request::route()->getName() == 'bill.show') ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('bill.index')); ?>"><?php echo e(__('Bill')); ?></a>
                                                </li>
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'payment.index' || Request::route()->getName() == 'payment.create' || Request::route()->getName() == 'payment.edit') ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('payment.index')); ?>"><?php echo e(__('Payment')); ?></a>
                                                </li>
                                                <li class="dash-item  <?php echo e((Request::route()->getName() == 'debit.note' ) ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('debit.note')); ?>"><?php echo e(__('Debit Note')); ?></a>
                                                </li>
                                            </ul>
                                        </li>
                                    <?php endif; ?>
                                    <?php if( Gate::check('manage chart of account') ||  Gate::check('manage journal entry') ||   Gate::check('balance sheet report') ||  Gate::check('ledger report') ||  Gate::check('trial balance report')): ?>
                                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'chart-of-account' || Request::segment(1) == 'journal-entry' || Request::segment(2) == 'ledger' ||  Request::segment(2) == 'balance-sheet' ||  Request::segment(2) == 'trial-balance')? 'active dash-trigger' :''); ?>">
                                            <a class="dash-link" href="#"><?php echo e(__('Double Entry')); ?><span class="dash-arrow"><i data-feather="chevron-right"></i></span></a>
                                            <ul class="dash-submenu">
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'chart-of-account.index') ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('chart-of-account.index')); ?>"><?php echo e(__('Chart of Accounts')); ?></a>
                                                </li>
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'journal-entry.edit' || Request::route()->getName() == 'journal-entry.create' || Request::route()->getName() == 'journal-entry.index' || Request::route()->getName() == 'journal-entry.show') ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('journal-entry.index')); ?>"><?php echo e(__('Journal Account')); ?></a>
                                                </li>
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'report.ledger' ) ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('report.ledger')); ?>"><?php echo e(__('Ledger Summary')); ?></a>
                                                </li>
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'report.balance.sheet' ) ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('report.balance.sheet')); ?>"><?php echo e(__('Balance Sheet')); ?></a>
                                                </li>
                                                <li class="dash-item <?php echo e((Request::route()->getName() == 'trial.balance' ) ? ' active' : ''); ?>">
                                                    <a class="dash-link" href="<?php echo e(route('trial.balance')); ?>"><?php echo e(__('Trial Balance')); ?></a>
                                                </li>
                                            </ul>
                                        </li>
                                    <?php endif; ?>
                                    <?php if(\Auth::user()->type =='company'): ?>
                                        <li class="dash-item <?php echo e((Request::segment(1) == 'budget')?'active':''); ?>">
                                            <a class="dash-link" href="<?php echo e(route('budget.index')); ?>"><?php echo e(__('Budget Planner')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if(Gate::check('manage goal')): ?>
                                        <li class="dash-item <?php echo e((Request::segment(1) == 'goal')?'active':''); ?>">
                                            <a class="dash-link" href="<?php echo e(route('goal.index')); ?>"><?php echo e(__('Financial Goal')); ?></a>
                                        </li>
                                    <?php endif; ?>
                                    <?php if(Gate::check('manage constant tax') || Gate::check('manage constant category') ||Gate::check('manage constant unit') ||Gate::check('manage constant payment method') ||Gate::check('manage constant custom field') ): ?>
                                        <li class="dash-item <?php echo e((Request::segment(1) == 'taxes' || Request::segment(1) == 'product-category' || Request::segment(1) == 'product-unit' || Request::segment(1) == 'payment-method' || Request::segment(1) == 'custom-field' || Request::segment(1) == 'chart-of-account-type')? 'active dash-trigger' :''); ?>">
                                            <a class="dash-link" href="<?php echo e(route('taxes.index')); ?>"><?php echo e(__('Accounting Setup')); ?></a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if(Gate::check('manage print settings')): ?>
                                        <li class="dash-item <?php echo e((Request::route()->getName() == 'print-setting') ? ' active' : ''); ?>">
                                            <a class="dash-link" href="<?php echo e(route('print.setting')); ?>"><?php echo e(__('Print Settings')); ?></a>
                                        </li>
                                    <?php endif; ?>

                                </ul>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!--------------------- End Account ----------------------------------->

                    <!--------------------- Start CRM ----------------------------------->










































                    <!--------------------- End CRM ----------------------------------->

                    <!--------------------- Start Project ----------------------------------->




































































                    <!--------------------- End Project ----------------------------------->



                    <!--------------------- Start User Managaement System ----------------------------------->

                    <?php if(\Auth::user()->type!='super admin' && ( Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage client'))): ?>
                        <li class="dash-item dash-hasmenu">
                            <a href="#!" class="dash-link <?php echo e((Request::segment(1) == 'users' || Request::segment(1) == 'roles' || Request::segment(1) == 'clients')?' active dash-trigger':''); ?>"
                            ><span class="dash-micon"><i class="ti ti-users"></i></span
                                ><span class="dash-mtext"><?php echo e(__('User Management')); ?></span
                                ><span class="dash-arrow"><i data-feather="chevron-right"></i></span
                                ></a>
                            <ul class="dash-submenu">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage user')): ?>
                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'users.index' || Request::route()->getName() == 'users.create' || Request::route()->getName() == 'users.edit') ? ' active' : ''); ?>">
                                        <a class="dash-link" href="<?php echo e(route('users.index')); ?>"><?php echo e(__('User')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage role')): ?>
                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'roles.index' || Request::route()->getName() == 'roles.create' || Request::route()->getName() == 'roles.edit') ? ' active' : ''); ?> ">
                                        <a class="dash-link" href="<?php echo e(route('roles.index')); ?>"><?php echo e(__('Role')); ?></a>
                                    </li>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage client')): ?>
                                    <li class="dash-item <?php echo e((Request::route()->getName() == 'clients.index' || Request::segment(1) == 'clients' || Request::route()->getName() == 'clients.edit') ? ' active' : ''); ?>">
                                        <a class="dash-link" href="<?php echo e(route('clients.index')); ?>"><?php echo e(__('Client')); ?></a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!--------------------- End User Managaement System----------------------------------->


                    <!--------------------- Start Products System ----------------------------------->

























                    <!--------------------- End Products System ----------------------------------->


                    <!--------------------- Start POs System ----------------------------------->






































                    <!--------------------- End POs System ----------------------------------->



















                    <!--------------
                    ------- Start System Setup ----------------------------------->

                    <?php if((\Auth::user()->type != 'super admin')): ?>

                        <?php if( Gate::check('manage company plan') || Gate::check('manage order') || Gate::check('manage company settings')): ?>
                            <li class="dash-item dash-hasmenu">
                                <a href="#!" class="dash-link ">
                                    <span class="dash-micon"><i class="ti ti-settings"></i></span><span class="dash-mtext"><?php echo e(__('Settings')); ?></span><span class="dash-arrow">
                                            <i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="dash-submenu">
                                    <?php if(Gate::check('manage company settings')): ?>
                                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'settings') ? ' active' : ''); ?>">
                                            <a href="<?php echo e(route('settings')); ?>" class="dash-link"><?php echo e(__('System Settings')); ?></a>
                                        </li>
                                    <?php endif; ?>











                                </ul>
                            </li>
                        <?php endif; ?>

                   <?php endif; ?>



                    <!--------------------- End System Setup ----------------------------------->
                </ul>
                <?php endif; ?>
            <?php if((\Auth::user()->type == 'client')): ?>
                <ul class="dash-navbar">
                    <?php if(Gate::check('manage client dashboard')): ?>
                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'dashboard') ? ' active' : ''); ?>">
                            <a href="<?php echo e(route('client.dashboard.view')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-home"></i></span><span class="dash-mtext"><?php echo e(__('Dashboard')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage deal')): ?>
                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'deals') ? ' active' : ''); ?>">
                            <a href="<?php echo e(route('deals.index')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-rocket"></i></span><span class="dash-mtext"><?php echo e(__('Deals')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage contract')): ?>
                            <li class="dash-item dash-hasmenu <?php echo e((Request::route()->getName() == 'contract.index' || Request::route()->getName() == 'contract.show')?'active':''); ?>">
                                <a href="<?php echo e(route('contract.index')); ?>" class="dash-link">
                                    <span class="dash-micon"><i class="ti ti-rocket"></i></span><span class="dash-mtext"><?php echo e(__('Contract')); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php if(Gate::check('manage project')): ?>
                        <li class="dash-item dash-hasmenu  <?php echo e((Request::segment(1) == 'projects') ? ' active' : ''); ?>">
                            <a href="<?php echo e(route('projects.index')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-share"></i></span><span class="dash-mtext"><?php echo e(__('Project')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                        <?php if(Gate::check('manage project')): ?>

                            <li class="dash-item  <?php echo e((Request::route()->getName() == 'project_report.index' || Request::route()->getName() == 'project_report.show') ? 'active' : ''); ?>">
                                <a class="dash-link" href="<?php echo e(route('project_report.index')); ?>">
                                    <span class="dash-micon"><i class="ti ti-chart-line"></i></span><span class="dash-mtext"><?php echo e(__('Project Report')); ?></span>
                                </a>
                            </li>
                        <?php endif; ?>

                    <?php if(Gate::check('manage project task')): ?>
                        <li class="dash-item dash-hasmenu  <?php echo e((Request::segment(1) == 'taskboard') ? ' active' : ''); ?>">
                            <a href="<?php echo e(route('taskBoard.view', 'list')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-list-check"></i></span><span class="dash-mtext"><?php echo e(__('Tasks')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage bug report')): ?>
                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'bugs-report') ? ' active' : ''); ?>">
                            <a href="<?php echo e(route('bugs.view','list')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-bug"></i></span><span class="dash-mtext"><?php echo e(__('Bugs')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage timesheet')): ?>
                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'timesheet-list') ? ' active' : ''); ?>">
                            <a href="<?php echo e(route('timesheet.list')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-clock"></i></span><span class="dash-mtext"><?php echo e(__('Timesheet')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage project task')): ?>
                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'calendar') ? ' active' : ''); ?>">
                            <a href="<?php echo e(route('task.calendar',['all'])); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-calendar"></i></span><span class="dash-mtext"><?php echo e(__('Task Calender')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                        <li class="dash-item dash-hasmenu">
                        <a href="<?php echo e(route('support.index')); ?>" class="dash-link <?php echo e((Request::segment(1) == 'support')?'active':''); ?>">
                            <span class="dash-micon"><i class="ti ti-headphones"></i></span><span class="dash-mtext"><?php echo e(__('Support')); ?></span>
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
            <?php if((\Auth::user()->type == 'super admin')): ?>
                <ul class="dash-navbar">
                    <?php if(Gate::check('manage super admin dashboard')): ?>
                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'dashboard') ? ' active' : ''); ?>">
                            <a href="<?php echo e(route('client.dashboard.view')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-home"></i></span><span class="dash-mtext"><?php echo e(__('Dashboard')); ?></span>
                            </a>
                        </li>

                    <?php endif; ?>


                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('manage user')): ?>
                        <li class="dash-item dash-hasmenu <?php echo e((Request::route()->getName() == 'users.index' || Request::route()->getName() == 'users.create' || Request::route()->getName() == 'users.edit') ? ' active' : ''); ?>">
                            <a href="<?php echo e(route('users.index')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-users"></i></span><span class="dash-mtext"><?php echo e(__('User')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php if(Gate::check('manage plan')): ?>
                        <li class="dash-item dash-hasmenu  <?php echo e((Request::segment(1) == 'plans')?'active':''); ?>">
                            <a href="<?php echo e(route('plans.index')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-trophy"></i></span><span class="dash-mtext"><?php echo e(__('Plan')); ?></span>
                            </a>
                        </li>

                    <?php endif; ?>
                    <?php if(\Auth::user()->type=='super admin'): ?>
                        <li class="dash-item dash-hasmenu <?php echo e(request()->is('plan_request*') ? 'active' : ''); ?>">
                            <a href="<?php echo e(route('plan_request.index')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-arrow-up-right-circle"></i></span><span class="dash-mtext"><?php echo e(__('Plan Request')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage coupon')): ?>
                        <li class="dash-item dash-hasmenu <?php echo e((Request::segment(1) == 'coupons')?'active':''); ?>">
                            <a href="<?php echo e(route('coupons.index')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-gift"></i></span><span class="dash-mtext"><?php echo e(__('Coupon')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if(Gate::check('manage order')): ?>
                        <li class="dash-item dash-hasmenu  <?php echo e((Request::segment(1) == 'orders')?'active':''); ?>">
                            <a href="<?php echo e(route('order.index')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-shopping-cart-plus"></i></span><span class="dash-mtext"><?php echo e(__('Order')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                        <li class="dash-item dash-hasmenu <?php echo e(Request::segment(1) == 'email_template' || Request::route()->getName() == 'manage.email.language' ? ' active dash-trigger' : 'collapsed'); ?>">
                            <a href="<?php echo e(route('manage.email.language',[$emailTemplate ->id,\Auth::user()->lang])); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-template"></i></span>
                                <span class="dash-mtext"><?php echo e(__('Email Template')); ?></span></a>
                        </li>

                    <?php if(Gate::check('manage system settings')): ?>
                        <li class="dash-item dash-hasmenu <?php echo e((Request::route()->getName() == 'systems.index') ? ' active' : ''); ?>">
                            <a href="<?php echo e(route('systems.index')); ?>" class="dash-link">
                                <span class="dash-micon"><i class="ti ti-settings"></i></span><span class="dash-mtext"><?php echo e(__('Settings')); ?></span>
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php /**PATH /Applications/MAMP/htdocs/erp/resources/views/partials/admin/menu.blade.php ENDPATH**/ ?>
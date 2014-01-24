<?php
/*
 *   Copyright (C) 2003-2009 Innoteam
 *
 */

// ----- Initialization -----
//

require_once('innowork/contracts/InnoworkContract.php');
require_once('innomatic/wui/Wui.php');
require_once('innomatic/wui/widgets/WuiWidget.php');
require_once('innomatic/wui/widgets/WuiContainerWidget.php');
require_once('innomatic/wui/dispatch/WuiEventsCall.php');
require_once('innomatic/wui/dispatch/WuiEvent.php');
require_once('innomatic/wui/dispatch/WuiEventRawData.php');
require_once('innomatic/wui/dispatch/WuiDispatcher.php');
require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php');
require_once('innowork/groupware/InnoworkCompany.php');
require_once('innowork/projects/InnoworkProject.php');

    global $gLocale, $gPage_title, $gXml_def, $gPage_status, $gInnowork_core;

require_once('innowork/core/InnoworkCore.php');
$gInnowork_core = InnoworkCore::instance('innoworkcore',
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
    );

$gLocale = new LocaleCatalog(
    'innowork-contracts::domain_main',
    \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getLanguage()
    );

$gWui = Wui::instance('wui');
$gWui->loadWidget( 'xml' );
$gWui->loadWidget( 'innomaticpage' );
$gWui->loadWidget( 'innomatictoolbar' );
$gWui->loadWidget( 'table' );

$gXml_def = $gPage_status = '';
$gPage_title = $gLocale->getStr( 'contracts.title' );
$gCore_toolbars = $gInnowork_core->getMainToolBar();
$gToolbars['contracts'] = array(
    'contracts' => array(
        'label' => $gLocale->getStr( 'contracts.toolbar' ),
        'themeimage' => 'listdetailed',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString( 'innoworkcontracts', array( array(
            'view',
            'default',
            array(
                'done' => 'false'
                ) ) ) )
        ),
    'archivedcontracts' => array(
        'label' => $gLocale->getStr( 'archived_contracts.toolbar' ),
        'themeimage' => 'listdetailed',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString( 'innoworkcontracts', array( array(
            'view',
            'default',
            array(
                'done' => 'true'
                ) ) ) )
        ),
    'newcontract' => array(
        'label' => $gLocale->getStr( 'newcontract.toolbar' ),
        'themeimage' => 'filenew',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString( 'innoworkcontracts', array( array(
            'view',
            'newcontract',
            '' ) ) )
        )
    );

$gToolbars['prefs'] = array(
    'prefs' => array(
        'label' => $gLocale->getStr( 'preferences.toolbar' ),
        'themeimage' => 'settings1',
        'horiz' => 'true',
        'action' => WuiEventsCall::buildEventsCallString( 'innoworkcontractsprefs', array( array(
            'view',
            'default',
            '' ) ) )
        )
    );

// ----- Action dispatcher -----
//
$gAction_disp = new WuiDispatcher( 'action' );

$gAction_disp->addEvent(
    'newcontract',
    'action_newcontract'
    );
function action_newcontract($eventData)
{
    global $gLocale, $gPage_status;

    $innowork_contract = new InnoworkContract(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

    $country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );

    $eventData['subscriptiondate'] = $country->getDateArrayFromShortDateStamp( $eventData['subscriptiondate'] );
    $eventData['expirationdate'] = $country->getDateArrayFromShortDateStamp( $eventData['expirationdate'] );

    $innowork_contract->Create(
        $eventData,
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

    $GLOBALS['innowork-contract']['newcontractid'] = $innowork_contract->mItemId;

    $gPage_status = $gLocale->getStr( 'contract_added.status' );
}

$gAction_disp->addEvent(
    'editcontract',
    'action_editcontract'
    );
function action_editcontract($eventData)
{
    global $gLocale, $gPage_status;

    $innowork_contract = new InnoworkContract(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['id']
        );

    $country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );

    $eventData['subscriptiondate'] = $country->getDateArrayFromShortDateStamp( $eventData['subscriptiondate'] );
    $eventData['expirationdate'] = $country->getDateArrayFromShortDateStamp( $eventData['expirationdate'] );

    $innowork_contract->Edit(
        $eventData,
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

    $gPage_status = $gLocale->getStr( 'contract_updated.status' );
}

$gAction_disp->addEvent(
    'removecontract',
    'action_removecontract'
    );
function action_removecontract($eventData)
{
    global $gLocale, $gPage_status;

    $innowork_contract = new InnoworkContract(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['id']
        );

    $innowork_contract->Trash();

    $gPage_status = $gLocale->getStr( 'contract_removed.status' );
}

$gAction_disp->addEvent(
    'setdone',
    'action_setdone'
    );
function action_setdone($eventData)
{
    global $gLocale, $gPage_status;

    $innowork_contract = new InnoworkContract(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['id']
        );

    $innowork_contract->Edit(
        array(
            'done' => $eventData['done'] == 'true' ? true : false
            ),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

    $gPage_status = $gLocale->getStr( 'contract_updated.status' );
}

$gAction_disp->addEvent(
        'createexpiration',
        'action_createexpiration'
        );
function action_createexpiration($eventData)
{
    global $gLocale, $gPage_status;
    $innowork_contract = new InnoworkContract(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['contractid']
        );

    require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php');
    $country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );
    $eventData['expirationdate'] = $country->getDateArrayFromShortDateStamp($eventData['newexpirationdate']);

    if ( $innowork_contract->CreateExpiration( $eventData ) )
        $gPage_status = $gLocale->getStr( 'addexpiration_ok.status' );
            else $gPage_status = $gLocale->getStr( 'addexpiration_error.status' );
}

$gAction_disp->addEvent(
        'removeexpiration',
        'action_removeexpiration'
        );
function action_removeexpiration($eventData)
{
    global $gLocale, $gPage_status;

    $innowork_contract = new InnoworkContract(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['contractid']
        );

    if ( $innowork_contract->RemoveExpiration( $eventData['id'] ) )$gPage_status = $gLocale->getStr( 'removeexpiration_ok.status' );
    else $gPage_status = $gLocale->getStr( 'removeexpiration_error.status' );
}

$gAction_disp->addEvent(
        'editexpiration',
        'action_editexpiration'
        );
function action_editexpiration($eventData)
{
    global $gLocale_country, $gPage_status, $gLocale;

    $innowork_contract = new InnoworkContract(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['contractid']
        );

    $params = array();
    $params['description']= $eventData['description'.$eventData['id']];
    $params['amount']=$eventData['amount'.$eventData['id']];
    $params['expirationdate']=$eventData['expirationdate'.$eventData['id']];
    require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php');
    $country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );
    $params['expirationdate']= $country->getDateArrayFromShortDateStamp($params['expirationdate']);


    if ( $innowork_contract->EditExpiration( $params, $eventData['id'] ) ) $gPage_status = $gLocale->getStr( 'editexpiration_ok.status' );
    else $gPage_status = $gLocale->getStr( 'editexpiration_error.status' );
}

$gAction_disp->Dispatch();

// ----- Main dispatcher -----
//
$gMain_disp = new WuiDispatcher( 'view' );

function contracts_list_action_builder($pageNumber)
{
    return WuiEventsCall::buildEventsCallString( '', array( array(
            'view',
            'default',
            array( 'pagenumber' => $pageNumber )
        ) ) );
}

$gMain_disp->addEvent(
    'default',
    'main_default'
    );
function main_default($eventData)
{
    global $gLocale, $gPage_title, $gXml_def, $gPage_status, $gInnowork_core;

    require_once('shared/wui/WuiSessionkey.php');

// Account managers

$users_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->Execute(
    'SELECT username,lname,fname '.
    'FROM domain_users '.
    'WHERE username<>'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->formatText( User::getAdminUsername(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId())).' '.
    'ORDER BY lname,fname'
    );

$gUsers[0] = $gLocale->getStr( 'all_account_managers.label' );

while ( !$users_query->eof ) {
    $gUsers[$users_query->getFields( 'username' )] = $users_query->getFields( 'lname' ).' '.$users_query->getFields( 'fname' );
    $users_query->moveNext();
}

$users_query->free();

// Customers

    $innowork_customers = new InnoworkCompany(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

    $customers_search = $innowork_customers->Search(
        '',
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

    $customers[0] = $gLocale->getStr( 'all_customers.label' );

    foreach ( $customers_search as $id => $data ) {
        $customers[$id] = $data['companyname'];
    }

    // Filtering

    $search_keys = array();

    if ( isset($eventData['filter'] ) ) {
        // Customer

        $customer_filter_sk = new WuiSessionKey(
            'customer_filter',
            array(
                'value' => $eventData['filter_customerid']
                )
            );

        if ( $eventData['filter_customerid'] != 0 ) $search_keys['customerid'] = $eventData['filter_customerid'];

        // Account manager

        $account_manager_filter_sk = new WuiSessionKey(
            'account_manager_filter',
            array(
                'value' => $eventData['filter_account_manager']
                )
            );

        if ( $eventData['filter_account_manager'] != '0' ) $search_keys['accountmanager'] = $eventData['filter_account_manager'];
         // Year

        $year_filter_sk = new WuiSessionKey(
            'year_filter',
            array(
                'value' => isset($eventData['filter_year'] ) ? $eventData['filter_year'] : ''
                )
            );

          // Month

        $month_filter_sk = new WuiSessionKey(
            'month_filter',
            array(
                'value' => isset($eventData['filter_month'] ) ? $eventData['filter_month'] : ''
                )
            );
    } else {
        // Customer

        $customer_filter_sk = new WuiSessionKey( 'customer_filter' );
        if (
            strlen( $customer_filter_sk->mValue )
            and $customer_filter_sk->mValue != 0
            ) $search_keys['customerid'] = $customer_filter_sk->mValue;
        $eventData['filter_customerid'] = $customer_filter_sk->mValue;

        // Account manager

        $account_manager_filter_sk = new WuiSessionKey( 'account_manager_filter' );
        if (
            strlen( $account_manager_filter_sk->mValue )
            and $account_manager_filter_sk->mValue != '0'
            ) $search_keys['accountmanager'] = $account_manager_filter_sk->mValue;
        $eventData['filter_account_manager'] = $account_manager_filter_sk->mValue;

        // Year

        $year_filter_sk = new WuiSessionKey( 'year_filter' );
        $eventData['filter_year'] = $year_filter_sk->mValue;

        // Month

        $month_filter_sk = new WuiSessionKey( 'month_filter' );
        $eventData['filter_month'] = $month_filter_sk->mValue;

    }

    if ( strlen( $eventData['filter_month'] ) &&  strlen( $eventData['filter_year'] ) )
    $search_keys['subscriptiondate'] = $eventData['filter_year'].'-'.$eventData['filter_month'];
    elseif ( strlen( $eventData['filter_month'] ) )
    $gPage_status = $gLocale->getStr( 'noyearmessage.status');
    elseif ( strlen( $eventData['filter_year'] ) )
    $search_keys['subscriptiondate'] = $year_filter_sk->mValue;



    if ( $search_keys['accountmanager'] == '0' ) unset( $search_keys['accountmanager'] );
    //if ( !count( $search_keys ) ) $search_keys = '';

    $tab_sess = new WuiSessionKey( 'innoworkcontractstab' );

    if ( !isset($eventData['done'] ) ) $eventData['done'] = $tab_sess->mValue;
    if ( !strlen( $eventData['done'] ) ) $eventData['done'] = 'false';

    // Sorting
                        $table = new WuiTable(
                            'contracts_done_'.$eventData['done'],
                            array(
                                'sessionobjectusername' => $eventData['done'] == 'true' ? 'done' : 'undone'
                                )
                            );

    $sort_by = 0;
    if ( strlen( $table->mSortDirection ) ) $sort_order = $table->mSortDirection;
    else $sort_order = 'down';

    if ( isset($eventData['sortby'] ) ) {
        if ( $table->mSortBy == $eventData['sortby'] ) {
            $sort_order = $sort_order == 'down' ? 'up' : 'down';
        } else {
            $sort_order = 'down';
        }

        $sort_by = $eventData['sortby'];
    } else {
        if ( strlen( $table->mSortBy ) ) $sort_by = $table->mSortBy;
    }

    $innowork_contracts = new InnoworkContract(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

    switch ( $sort_by ) {
    case '0':
        $innowork_contracts->mSearchOrderBy = 'number'.( $sort_order == 'up' ? ' DESC' : '' );
        break;
    case '1':
        $innowork_contracts->mSearchOrderBy = 'customerid'.( $sort_order == 'up' ? ' DESC' : '' );
        break;
    case '2':
        $innowork_contracts->mSearchOrderBy = 'description'.( $sort_order == 'up' ? ' DESC' : '' );
        break;
    case '3':
        $innowork_contracts->mSearchOrderBy = 'subscriptiondate'.( $sort_order == 'up' ? ' DESC' : '' );
        break;
    case '4':
        $innowork_contracts->mSearchOrderBy = 'expirationdate'.( $sort_order == 'up' ? ' DESC' : '' );
        break;
    case '5':
        $innowork_contracts->mSearchOrderBy = 'contractvalue'.( $sort_order == 'up' ? ' DESC' : '' );
        break;
    }

    $headers[0]['label'] = $gLocale->getStr( 'number.header' );
    $headers[0]['link'] = WuiEventsCall::buildEventsCallString( '',
            array( array(
                    'view',
                    'default',
                    array( 'sortby' => '0' )
                    ) ) );
    $headers[1]['label'] = $gLocale->getStr( 'customer.header' );
    $headers[1]['link'] = WuiEventsCall::buildEventsCallString( '',
            array( array(
                    'view',
                    'default',
                    array( 'sortby' => '1' )
                    ) ) );
    $headers[2]['label'] = $gLocale->getStr( 'description.header' );
    $headers[2]['link'] = WuiEventsCall::buildEventsCallString( '',
            array( array(
                    'view',
                    'default',
                    array( 'sortby' => '2' )
                    ) ) );
    $headers[3]['label'] = $gLocale->getStr( 'subscriptiondate.header' );
    $headers[3]['link'] = WuiEventsCall::buildEventsCallString( '',
            array( array(
                    'view',
                    'default',
                    array( 'sortby' => '3' )
                    ) ) );
    $headers[4]['label'] = $gLocale->getStr( 'expirationdate.header' );
    $headers[4]['link'] = WuiEventsCall::buildEventsCallString( '',
            array( array(
                    'view',
                    'default',
                    array( 'sortby' => '4' )
                    ) ) );
    $headers[5]['label'] = $gLocale->getStr( 'price.header' );
    $headers[5]['link'] = WuiEventsCall::buildEventsCallString( '',
            array( array(
                    'view',
                    'default',
                    array( 'sortby' => '5' )
                    ) ) );

    $tab_sess = new WuiSessionKey(
        'innoworkcontractstab',
        array(
            'value' => isset($eventData['done'] ) ? $eventData['done'] : ''
            )
        );

    if (
        isset($eventData['done'] )
        and $eventData['done'] == 'true'
        )
    {
        $done_check = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmttrue;
        $search_keys['done'] = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmttrue;
        $done_icon = 'buttonok';
        $done_action = 'false';
        $done_label = 'setundone.button';
    } else {
        $done_check = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmtfalse;
        $search_keys['done'] = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmtfalse;
        $done_icon = 'redo';
        $done_action = 'true';
        $done_label = 'setdone.button';
    }

    $contracts_search = $innowork_contracts->Search(
        $search_keys,
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

    $num_contracts = count( $contracts_search );

    $total_value = $total_contracts = 0;

        $gXml_def =
'<vertgroup>
  <children>

    <label><name>filter</name>
      <args>
        <bold>true</bold>
        <label type="encoded">'.urlencode( $gLocale->getStr( 'filter.label' ) ).'</label>
      </args>
    </label>

    <form><name>filter</name>
      <args>
            <action type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    array(
                        'filter' => 'true'
                        )
                    )
            ) ) ).'</action>
      </args>
      <children>

        <grid>
          <children>

    <label row="0" col="0"><name>year</name>
      <args>
        <label type="encoded">'.urlencode( $gLocale->getStr( 'filter_year.label' ) ).'</label>
      </args>
    </label>

    <horizgroup row="0" col="1">
    <children>

    <string><name>filter_year</name>
      <args>
        <disp>view</disp>
        <size>4</size>
        <value type="encoded">'.urlencode( isset($eventData['filter_year'] ) ? $eventData['filter_year'] : '' ).'</value>
      </args>
    </string>

    <string><name>filter_month</name>
      <args>
        <disp>view</disp>
        <size>2</size>
        <value type="encoded">'.urlencode( isset($eventData['filter_month'] ) ? $eventData['filter_month'] : '' ).'</value>
      </args>
    </string>

    </children>
    </horizgroup>


        <button row="0" col="3"><name>filter</name>
          <args>
            <themeimage>zoom</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <formsubmit>filter</formsubmit>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'filter.submit' ) ).'</label>
            <action type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    array(
                        'filter' => 'true'
                        )
                    )
            ) ) ).'</action>
          </args>
        </button>

    <label row="1" col="0"><name>customer</name>
      <args>
        <label type="encoded">'.urlencode( $gLocale->getStr( 'filter_customer.label' ) ).'</label>
      </args>
    </label>
    <combobox row="1" col="1"><name>filter_customerid</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode( $customers ).'</elements>
        <default type="encoded">'.urlencode( isset($eventData['filter_customerid'] ) ? $eventData['filter_customerid'] : '' ).'</default>
      </args>
    </combobox>

    <label row="2" col="0">
      <args>
        <label type="encoded">'.urlencode( $gLocale->getStr( 'filter_account_manager.label' ) ).'</label>
      </args>
    </label>
    <combobox row="2" col="1"><name>filter_account_manager</name>
      <args>
        <disp>view</disp>
        <elements type="array">'.WuiXml::encode( $gUsers ).'</elements>
        <default type="encoded">'.urlencode( isset($eventData['filter_account_manager'] ) ? $eventData['filter_account_manager'] : '' ).'</default>
      </args>
    </combobox>

          </children>
        </grid>

      </children>
    </form>

    <horizbar/>


  <label>
    <args>
      <label type="encoded">'.urlencode( $gLocale->getStr( ( $eventData['done'] == 'true' ? 'done' : '' ).'contracts.label' ) ).'</label>
      <bold>true</bold>
    </args>
  </label>

<table><name>contracts_done_'.$eventData['done'].'</name>
  <args>
    <headers type="array">'.WuiXml::encode( $headers ).'</headers>
    <rowsperpage>10</rowsperpage>
    <pagesactionfunction>contracts_list_action_builder</pagesactionfunction>
    <pagenumber>'.( isset($eventData['pagenumber'] ) ? $eventData['pagenumber'] : '' ).'</pagenumber>
    <sessionobjectusername>'.( $eventData['done'] == 'true' ? 'done' : 'undone' ).'</sessionobjectusername>
    <sortby>'.$sort_by.'</sortby>
    <sortdirection>'.$sort_order.'</sortdirection>
    <rows>'.$num_contracts.'</rows>
  </args>
  <children>';

        $row = 0;
    $page = 1;

                    if ( isset($eventData['pagenumber'] ) ) {
                        $page = $eventData['pagenumber'];
                    } else {
                        require_once('shared/wui/WuiTable.php');

                        $table = new WuiTable(
                            'contracts_done_'.$eventData['done'],
                            array(
                                'sessionobjectusername' => $eventData['done'] == 'true' ? 'done' : 'undone'
                                )
                            );

                        $page = $table->mPageNumber;
                    }
                    if ( $page > ceil( $num_contracts / 10 ) ) $page = ceil( $num_contracts / 10 );

                    $from = ( $page * 10 ) - 10;
                    $to = $from + 10 - 1;

        $summaries = $gInnowork_core->getSummaries();

        $locale_country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );

        foreach ( $contracts_search as $id => $contract ) {
            $total_contracts++;
            $total_value += $contract['contractvalue'];

        if ( $row >= $from and $row <= $to ) {
            $tmp_customer = new InnoworkCompany(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                $contract['customerid']
                );

            $tmp_customer_data = $tmp_customer->getItem();

            $tmp_project = new InnoworkProject(
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
                $contract['projectid']
                );

            $tmp_project_data = $tmp_project->getItem();

            $subscription_date_array = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->getDateArrayFromTimestamp( $contract['subscriptiondate'] );
            $subscription_date = $locale_country->FormatShortArrayDate( $subscription_date_array );

            $expiration_date_array = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->getDateArrayFromTimestamp( $contract['expirationdate'] );
            $expiration_date = $locale_country->FormatShortArrayDate( $expiration_date_array );

            $gXml_def .=
'<label row="'.$row.'" col="0">
  <args>
    <label type="encoded">'.urlencode( $contract['number'] ).'</label>
    <compact>true</compact>
  </args>
</label>

<vertgroup row="'.$row.'" col="1">
  <children>

    <link>
      <args>
        <link type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString(
            $summaries['directorycompany']['domainpanel'],
            array(
                array(
                    $summaries['directorycompany']['showdispatcher'],
                    $summaries['directorycompany']['showevent'],
                    array( 'id' => $contract['customerid'] )
                    )
                )
            ) ).'</link>
        <label type="encoded">'.urlencode( '<strong>'.$tmp_customer_data['companyname'].'</strong>' ).'</label>
        <compact>true</compact>
      </args>
    </link>

    <link>
      <args>
        <link type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString(
            $summaries['project']['domainpanel'],
            array(
                array(
                    $summaries['project']['showdispatcher'],
                    $summaries['project']['showevent'],
                    array( 'id' => $contract['projectid'] )
                    )
                )
            ) ).'</link>
        <label type="encoded">'.urlencode( $tmp_project_data['name'] ).'</label>
        <compact>true</compact>
      </args>
    </link>

  </children>
</vertgroup>

<label row="'.$row.'" col="2">
  <args>
    <label type="encoded">'.urlencode( strlen( $contract['description'] ) > 50 ?
        substr( $contract['description'], 0, 47 ).'...' :
        $contract['description'] ).'</label>
    <compact>true</compact>
  </args>
</label>

<label row="'.$row.'" col="3">
  <args>
    <label type="encoded">'.urlencode( $subscription_date ).'</label>
    <compact>true</compact>
  </args>
</label>

<label row="'.$row.'" col="4">
  <args>
    <label type="encoded">'.urlencode( $expiration_date ).'</label>
    <compact>true</compact>
  </args>
</label>

<label row="'.$row.'" col="5" halign="right">
  <args>
    <label type="encoded">'.urlencode( $locale_country->FormatMoney( $contract['contractvalue'] ) ).'</label>
    <compact>true</compact>
  </args>
</label>

<innomatictoolbar row="'.$row.'" col="6">
  <args>
    <frame>false</frame>
    <toolbars type="array">'.WuiXml::encode( array(
        'view' => array(
            'show' => array(
                'label' => $gLocale->getStr( 'showcontract.button' ),
                'themeimage' => 'zoom',
                'horiz' => 'true',
                'compact' => 'true',
                'action' => WuiEventsCall::buildEventsCallString( '', array( array(
                    'view',
                    'showcontract',
                    array( 'id' => $id ) ) ) )
                ),
            'done' => array(
                'label' => $gLocale->getStr( $done_label ),
                'themeimage' => $done_icon,
                'horiz' => 'true',
                'action' => WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    ''
                ),
                array(
                    'action',
                    'setdone',
                    array( 'id' => $id, 'done' => $done_action ) ) ) )
                ),
            'remove' => array(
                'label' => $gLocale->getStr( 'removecontract.button' ),
                'themeimage' => 'trash',
                'horiz' => 'true',
                'compact' => 'true',
                'needconfirm' => 'true',
                'confirmmessage' => $gLocale->getStr( 'removecontract.confirm' ),
                'action' => WuiEventsCall::buildEventsCallString( '', array(
                    array(
                        'view',
                        'default',
                        ''
                    ),
                    array(
                        'action',
                        'removecontract',
                        array( 'id' => $id ) ) ) )
        ) ) ) ).'</toolbars>
  </args>
</innomatictoolbar>';
            }
            $row++;
        }

        $gXml_def .=
'  </children>
</table>

    <horizbar/>

    <grid>
      <children>

        <label row="0" col="0">
          <args>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'total_contracts.label' ) ).'</label>
          </args>
        </label>

        <string row="0" col="1">
          <args>
            <readonly>true</readonly>
            <value type="encoded">'.urlencode( $total_contracts ).'</value>
            <size>12</size>
          </args>
        </string>

        <label row="1" col="0">
          <args>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'total_value.label' ) ).'</label>
          </args>
        </label>

        <string row="1" col="1">
          <args>
            <readonly>true</readonly>
            <value type="encoded">'.urlencode( $locale_country->FormatMoney( $total_value ) ).'</value>
            <size>12</size>
          </args>
        </string>

      </children>
    </grid>

  </children>
</vertgroup>';
}

$gMain_disp->addEvent(
    'newcontract',
    'main_newcontract'
    );
function main_newcontract($eventData)
{
    global $gXml_def, $gLocale, $gPage_title;

    // Companies list

    $innowork_companies = new InnoworkCompany(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
    $search_results = $innowork_companies->Search(
        '',
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

    $companies['0'] = $gLocale->getStr( 'nocustomer.label' );

    while ( list( $id, $fields ) = each( $search_results ) ) {
        $companies[$id] = $fields['companyname'];
    }

    // Projects list

    $innowork_projects = new InnoworkProject(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
    $search_results = $innowork_projects->Search(
        '',
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

    $projects['0'] = $gLocale->getStr( 'noproject.label' );

    while ( list( $id, $fields ) = each( $search_results ) ) {
        $projects[$id] = $fields['name'];
    }

// Account managers

$users_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->Execute(
    'SELECT username,lname,fname '.
    'FROM domain_users '.
    'WHERE username<>'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->formatText( User::getAdminUsername(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId())).' '.
    'ORDER BY lname,fname'
    );

$gUsers[''] = $gLocale->getStr( 'no_account_manager.label' );

while ( !$users_query->eof ) {
    $gUsers[$users_query->getFields( 'username' )] = $users_query->getFields( 'lname' ).' '.$users_query->getFields( 'fname' );
    $users_query->moveNext();
}

$users_query->free();

    /*
    $payments_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->Execute(
        'SELECT * '.
        'FROM innowork_billing_payments '.
        'ORDER BY description'
        );

    $payments['0'] = $gLocale->getStr( 'nopayment.label' );

    while ( !$payments_query->eof ) {
        $payments[$payments_query->getFields( 'id' )] = $payments_query->getFields( 'description' );
        $payments_query->moveNext();
    }
    */

    // Subscription date

    $locale_country = new LocaleCountry(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry()
        );

    $exp_date = $curr_date = $locale_country->getDateArrayFromSafeTimestamp(
        $locale_country->SafeFormatTimestamp()
        );

    $exp_date['year']++;

    // Contract number

    $innowork_contract = new InnoworkContract(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );

    $contract_number = (int)$innowork_contract->getLastContractNumber();
    $contract_number++;

    // Defaults

    $gXml_def .=
'<vertgroup>
  <children>

    <table><name>contract</name>
      <args>
        <headers type="array">'.WuiXml::encode(
            array( '0' => array(
                'label' => $gLocale->getStr( 'newcontract.label' )
                ) ) ).'</headers>
      </args>
      <children>

    <form row="0" col="0"><name>contract</name>
      <args>
        <method>post</method>
        <action type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    ''
                    ),
                array(
                    'action',
                    'newcontract',
                    '' )
            ) ) ).'</action>
      </args>
      <children>

        <grid>
          <children>

            <label row="0" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'number.label' ) ).'</label>
              </args>
            </label>
            <string row="0" col="1"><name>number</name>
              <args>
                <disp>action</disp>
                <size>5</size>
                <value>'.$contract_number.'</value>
              </args>
            </string>

            <label row="1" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'customer.label' ) ).'</label>
              </args>
            </label>
            <combobox row="1" col="1"><name>customerid</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $companies ).'</elements>
              </args>
            </combobox>

            <label row="2" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'project.label' ) ).'</label>
              </args>
            </label>
            <combobox row="2" col="1"><name>projectid</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $projects ).'</elements>
              </args>
            </combobox>

            <label row="3" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'account_manager.label' ) ).'</label>
              </args>
            </label>
            <combobox row="3" col="1"><name>accountmanager</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $gUsers ).'</elements>
              </args>
            </combobox>

            <label row="4" col="0" halign="" valign="top">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'description.label' ) ).'</label>
              </args>
            </label>
            <text row="4" col="1" halign="" valign="top"><name>description</name>
              <args>
                <disp>action</disp>
                <rows>10</rows>
                <cols>80</cols>
              </args>
            </text>

            <label row="5" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'subscriptiondate.label' ) ).'</label>
              </args>
            </label>
            <date row="5" col="1"><name>subscriptiondate</name>
              <args>
                <disp>action</disp>
                <value type="array">'.WuiXml::encode( $curr_date ).'</value>
              </args>
            </date>

            <label row="6" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'expirationdate.label' ) ).'</label>
              </args>
            </label>
            <date row="6" col="1"><name>expirationdate</name>
              <args>
                <disp>action</disp>
                <value type="array">'.WuiXml::encode( $exp_date ).'</value>
              </args>
            </date>

            <label row="7" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'price.label' ) ).'</label>
              </args>
            </label>
            <string row="7" col="1"><name>contractvalue</name>
              <args>
                <disp>action</disp>
                <size>15</size>
              </args>
            </string>

          </children>
        </grid>

        </children>
        </form>

        <button row="1" col="0"><name>apply</name>
          <args>
            <themeimage>buttonok</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <formsubmit>contract</formsubmit>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'newcontract.button' ) ).'</label>
            <action type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    ''
                    ),
                array(
                    'action',
                    'newcontract',
                    '' )
            ) ) ).'</action>
          </args>
        </button>

      </children>
    </table>

  </children>
</vertgroup>';
}

$gMain_disp->addEvent(
    'showcontract',
    'main_showcontract'
    );
function main_showcontract($eventData)
{
    global $gXml_def, $gLocale, $gPage_title;

    $innowork_contract = new InnoworkContract(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess(),
        $eventData['id']
        );

    $contract_data = $innowork_contract->getItem();
    $expirations_list = $innowork_contract->getExpirationList();

    if (
        $contract_data['done'] == \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmttrue
        )
    {
        $done_icon = 'buttonok';
        $done_action = 'false';
        $done_label = 'setundone.button';
    } else {
        $done_icon = 'redo';
        $done_action = 'true';
        $done_label = 'setdone.button';
    }

    $curr_date = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->getDateArrayFromTimestamp( $contract_data['subscriptiondate'] );
    $exp_date = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->getDateArrayFromTimestamp( $contract_data['expirationdate'] );
    // Companies list

    $innowork_companies = new InnoworkCompany(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
    $search_results = $innowork_companies->Search(
        '',
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

    $companies['0'] = $gLocale->getStr( 'nocustomer.label' );

    while ( list( $id, $fields ) = each( $search_results ) ) {
        $companies[$id] = $fields['companyname'];
    }

    // Projects list

    $innowork_projects = new InnoworkProject(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getDataAccess(),
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()
        );
    $search_results = $innowork_projects->Search(
        '',
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getUserId()
        );

    $projects['0'] = $gLocale->getStr( 'noproject.label' );

    while ( list( $id, $fields ) = each( $search_results ) ) {
        $projects[$id] = $fields['name'];
    }

// Account managers

$users_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->Execute(
    'SELECT username,lname,fname '.
    'FROM domain_users '.
    'WHERE username<>'.\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->formatText(User::getAdminUsername(\Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDomainId())).' '.
    'ORDER BY lname,fname'
    );

$gUsers[''] = $gLocale->getStr( 'no_account_manager.label' );

while ( !$users_query->eof ) {
    $gUsers[$users_query->getFields( 'username' )] = $users_query->getFields( 'lname' ).' '.$users_query->getFields( 'fname' );
    $users_query->moveNext();
}

$users_query->free();

    /*
    $payments_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->Execute(
        'SELECT * '.
        'FROM innowork_billing_payments '.
        'ORDER BY description'
        );

    $payments['0'] = $gLocale->getStr( 'nopayment.label' );

    while ( !$payments_query->eof ) {
        $payments[$payments_query->getFields( 'id' )] = $payments_query->getFields( 'description' );
        $payments_query->moveNext();
    }
    */

    // Subscription date

    $locale_country = new LocaleCountry(
        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry()
        );

    $today_date = $locale_country->getDateArrayFromSafeTimestamp(
        $locale_country->SafeFormatTimestamp()
        );
    $amount = $innowork_contract->getTotalAmount();
    // Defaults

    $gXml_def .=
'<horizgroup>
  <children>

  <vertgroup>
  <children>

    <table><name>contract</name>
      <args>
        <headers type="array">'.WuiXml::encode(
            array( '0' => array(
                'label' => $gLocale->getStr( 'editcontract.label' )
                ) ) ).'</headers>
      </args>
      <children>

    <form row="0" col="0"><name>contract</name>
      <args>
        <method>post</method>
        <action type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    ''
                    ),
                array(
                    'action',
                    'editcontract',
                    array(
                        'id' => $eventData['id']
                        ) )
            ) ) ).'</action>
      </args>
      <children>

        <grid>
          <children>

            <label row="0" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'number.label' ) ).'</label>
              </args>
            </label>
            <string row="0" col="1"><name>number</name>
              <args>
                <disp>action</disp>
                <size>5</size>
                <value type="encoded">'.$contract_data['number'].'</value>
              </args>
            </string>

            <label row="1" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'customer.label' ) ).'</label>
              </args>
            </label>
            <combobox row="1" col="1"><name>customerid</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $companies ).'</elements>
                <default>'.$contract_data['customerid'].'</default>
              </args>
            </combobox>

            <label row="2" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'project.label' ) ).'</label>
              </args>
            </label>
            <combobox row="2" col="1"><name>projectid</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $projects ).'</elements>
                <default>'.$contract_data['projectid'].'</default>
              </args>
            </combobox>

            <label row="3" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'account_manager.label' ) ).'</label>
              </args>
            </label>
            <combobox row="3" col="1"><name>accountmanager</name>
              <args>
                <disp>action</disp>
                <elements type="array">'.WuiXml::encode( $gUsers ).'</elements>
                <default>'.$contract_data['accountmanager'].'</default>
              </args>
            </combobox>

            <label row="4" col="0" halign="" valign="top">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'description.label' ) ).'</label>
              </args>
            </label>
            <text row="4" col="1" halign="" valign="top"><name>description</name>
              <args>
                <disp>action</disp>
                <rows>10</rows>
                <cols>80</cols>
                <value type="encoded">'.urlencode($contract_data['description']).'</value>
              </args>
            </text>

            <label row="5" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'subscriptiondate.label' ) ).'</label>
              </args>
            </label>
            <date row="5" col="1"><name>subscriptiondate</name>
              <args>
                <disp>action</disp>
                <value type="array">'.WuiXml::encode( $curr_date ).'</value>
              </args>
            </date>

            <label row="6" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'expirationdate.label' ) ).'</label>
              </args>
            </label>
            <date row="6" col="1"><name>expirationdate</name>
              <args>
                <disp>action</disp>
                <value type="array">'.WuiXml::encode( $exp_date ).'</value>
              </args>
            </date>


            <label row="7" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'price.label' ) ).'</label>
              </args>
            </label>

           <string row="7" col="1"><name>contractvalue</name>
              <args>
                <disp>action</disp>
                <size>15</size>
                <value type="encoded">'.$contract_data['contractvalue'].'</value>
              </args>
            </string>

            <label row="8" col="0">
              <args>
                <label type="encoded">'.urlencode( $gLocale->getStr( 'payments.label' ) ).'</label>
              </args>
            </label>

            <label row="8" col="1">
              <args>
                <label type="encoded">'.urlencode( $amount['amount'] ).'</label>
              </args>
            </label>


          </children>
        </grid>

        </children>
        </form>

        <horizgroup row="1" col="0">
          <children>

        <button><name>apply</name>
          <args>
            <themeimage>buttonok</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <formsubmit>contract</formsubmit>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'editcontract.button' ) ).'</label>
            <action type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    ''
                    ),
                array(
                    'action',
                    'editcontract',
                    array(
                        'id' => $eventData['id']
                        ) )
            ) ) ).'</action>
          </args>
        </button>

        <button><name>setdone</name>
          <args>
            <themeimage>'.$done_icon.'</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <label type="encoded">'.urlencode( $gLocale->getStr( $done_label ) ).'</label>
            <action type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    ''
                    ),
                array(
                    'action',
                    'setdone',
                    array(
                        'id' => $eventData['id'],
                        'done' => $done_action
                        ) )
            ) ) ).'</action>
          </args>
        </button>

        <button><name>trash</name>
          <args>
            <themeimage>trash</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'removecontract.button' ) ).'</label>
            <needconfirm>true</needconfirm>
            <confirmmessage type="encoded">'.urlencode( $gLocale->getStr( 'removecontract.confirm' ) ).'</confirmmessage>
            <action type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    ''
                    ),
                array(
                    'action',
                    'removecontract',
                    array(
                        'id' => $eventData['id']
                        ) )
            ) ) ).'</action>
          </args>
        </button>

        <button><name>close</name>
          <args>
            <themeimage>fileclose</themeimage>
            <horiz>true</horiz>
            <frame>false</frame>
            <label type="encoded">'.urlencode( $gLocale->getStr( 'close.button' ) ).'</label>
            <action type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( '', array(
                array(
                    'view',
                    'default',
                    ''
                    )
            ) ) ).'</action>
          </args>
        </button>

          </children>
        </horizgroup>

<vertgroup row="2" col="0"><name>mainVGroup</name>
<children>

   <label>
      <args>
        <label type="encoded">'.urlencode( $gLocale->getStr( 'expirationtitle.label' ) ).'</label>
        <bold>true</bold>
      </args>
   </label>

     <form><name>expirationForm</name>
        <args><action type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( '', array( array( 'view', 'showcontract', '' ) ) ) ).'</action></args>
        <children>
        <table>
            <name>expirationTable</name>
            <args>
                <headers type="array">'.WuiXml::encode( array(
                    0 => array(
                        'label' => $gLocale->getStr( 'date.headers' ) ),
                    1 => array(
                        'label' => $gLocale->getStr( 'description.headers' ) ),
                    2 => array(
                        'label' => $gLocale->getStr( 'amount.headers' ) )

                         ) ).'</headers>
            </args>
            <children>';

    $row = 0;
    foreach( $expirations_list as $expiration_data ) {
        $expiration_date = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->getDateArrayFromTimestamp( $expiration_data['expirationdate'] );

        $gXml_def .=
                '<date row="'.$row.'" col="0"><name>expirationdate'.$expiration_data['id'].'</name>
                  <args>
                    <disp>action</disp>
                    <value type="array">'.WuiXml::encode( $expiration_date ).'</value>
                  </args>
                </date>

                <string row="'.$row.'" col="1"><name>description'.$expiration_data['id'].'</name>
                <args><disp>action</disp><size>15</size>
                <value type="encoded">'.urlencode( $expiration_data['description'] ).'</value></args>
                </string>

                <string row="'.$row.'" col="2"><name>amount'.$expiration_data['id'].'</name>
                <args><disp>action</disp><size>6</size>
                <value type="encoded">'.urlencode( $expiration_data['amount'] ).'</value></args>
                </string>

                <innomatictoolbar row="'.$row.'" col="3"><name>expirationInnomaticToolBar</name>
                <args><toolbars type="array">'.WuiXml::encode( array(
                    'actions' => array(
                        'editButton' => array(
                            'label' => $gLocale->getStr( 'editexpiration.label' ),
                            'themeimage' => 'pencil',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true',
                            'formsubmit' => 'expirationForm',
                            'action' => WuiEventsCall::buildEventsCallString( '', array( array( 'view', 'showcontract', array('id' => $contract_data['id'] ) ),
                                array( 'action', 'editexpiration', array( 'id' => $expiration_data['id'], 'contractid' => $contract_data['id'] ) ) ) )
                            ),
                        'removeButton' => array(
                            'label' => $gLocale->getStr( 'removeexpiration.label' ),
                            'themeimage' => 'editdelete',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true',
                            'needconfirm' => 'true',
                            'confirmmessage' => sprintf( $gLocale->getStr( 'removeexpiration.message' ), '' ),
                            'action' => WuiEventsCall::buildEventsCallString( '', array( array( 'view', 'showcontract', array('id' => $contract_data['id'] ) ),
                                array( 'action', 'removeexpiration', array( 'id' => $expiration_data['id'], 'contractid' => $contract_data['id'] ) ) ) )
                            ) ) ) ).'</toolbars><frame>false</frame></args>
                </innomatictoolbar>';
        $row++;
    }

    $gXml_def .=
                '<date row="'.$row.'" col="0"><name>newexpirationdate</name>
                  <args>
                    <disp>action</disp>
                    <value type="array">'.WuiXml::encode( $today_date ).'</value>
                  </args>
                </date>

                <string row="'.$row.'" col="1"><name>description</name>
                <args><disp>action</disp><size>15</size></args>
                </string>

                <string row="'.$row.'" col="2"><name>amount</name>
                <args><disp>action</disp><size>6</size></args>
                </string>

                <innomatictoolbar row="'.$row.'" col="3"><name>cenetrInnomaticToolBar</name>
                <args><toolbars type="array">'.WuiXml::encode( array(
                    'actions' => array(
                        'addButton' => array(
                            'label' => $gLocale->getStr( 'addexpiration.label' ),
                            'themeimage' => 'editpaste',
                            'themeimagetype' => 'mini',
                            'horiz' => 'true',
                            'formsubmit' => 'expirationForm',
                            'action' => WuiEventsCall::buildEventsCallString( '', array( array( 'view', 'showcontract', array('id' => $contract_data['id'] ) ), array( 'action', 'createexpiration', array ( 'contractid' => $contract_data['id'] ) ) ) )
                            ) ) ) ).'</toolbars><frame>false</frame></args>
                </innomatictoolbar>

            </children>
        </table>
      </children>
    </form>

 </children>
</vertgroup>


      </children>
    </table>
  </children>
</vertgroup>

  <innoworkitemacl><name>itemacl</name>
    <args>
      <itemtype>contract</itemtype>
      <itemid>'.$eventData['id'].'</itemid>
      <itemownerid>'.$contract_data['ownerid'].'</itemownerid>
      <defaultaction type="encoded">'.urlencode( WuiEventsCall::buildEventsCallString( '', array(
        array( 'view', 'showcontract', array( 'id' => $eventData['id'] ) ) ) ) ).'</defaultaction>
    </args>
  </innoworkitemacl>

  </children>
</horizgroup>';
}

$gMain_disp->Dispatch();

// ----- Rendering -----
//
$gWui->addChild( new WuiInnomaticPage( 'page', array(
    'pagetitle' => $gPage_title,
    'icon' => 'moneydollar',
    'toolbars' => array(
        new WuiInnomaticToolbar(
            'view',
            array(
                'toolbars' => $gToolbars, 'toolbar' => 'true'
                ) ),
        new WuiInnomaticToolBar(
            'core',
            array(
                'toolbars' => $gCore_toolbars, 'toolbar' => 'true'
                ) ),
            ),
    'maincontent' => new WuiXml(
        'page', array(
            'definition' => $gXml_def
            ) ),
    'status' => $gPage_status
    ) ) );

$gWui->render();

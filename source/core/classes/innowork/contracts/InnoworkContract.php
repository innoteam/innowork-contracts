<?php
/*
 *   Copyright (C) 2003-2014 Innoteam
 *
 */
require_once('innowork/core/InnoworkItem.php');
require_once('innowork/core/InnoworkAcl.php');

class InnoworkContract extends InnoworkItem
{
    public $mTable = 'innowork_contracts';
    public $mNewDispatcher = 'view';
    public $mNewEvent = 'newcontract';
    public $mShowDispatcher = 'view';
    public $mShowEvent = 'showcontract';
    public $mNoTrash = false;
    public $mNoAcl = true;
    public $mNoLog = false;
    public $_mCreationAcl = InnoworkAcl::TYPE_PUBLIC;
    const ITEM_TYPE = 'contract';

    public function __construct($rrootDb, $rdomainDA, $contractId = 0)
    {
        parent::__construct(
            $rrootDb,
            $rdomainDA,
            InnoworkContract::ITEM_TYPE,
            $contractId
            );

        $this->mKeys['number'] = 'integer';
        $this->mKeys['description'] = 'text';
        $this->mKeys['customerid'] = 'table:innowork_directory_companies:companyname:integer';
        $this->mKeys['projectid'] = 'table:innowork_projects:name:integer';
        $this->mKeys['accountmanager'] = 'text';
        $this->mKeys['subscriptiondate'] = 'timestamp';
        $this->mKeys['expirationdate'] = 'timestamp';
        $this->mKeys['contractvalue'] = 'text';
        $this->mKeys['done'] = 'boolean';

        $this->mSearchResultKeys[] = 'number';
        $this->mSearchResultKeys[] = 'description';

        $this->mSearchResultKeys[] = 'customerid';

        $this->mSearchResultKeys[] = 'projectid';
        $this->mSearchResultKeys[] = 'accountmanager';
        $this->mSearchResultKeys[] = 'subscriptiondate';
        $this->mSearchResultKeys[] = 'expirationdate';
        $this->mSearchResultKeys[] = 'contractvalue';

        $this->mViewableSearchResultKeys[] = 'number';
        $this->mViewableSearchResultKeys[] = 'description';
        $this->mViewableSearchResultKeys[] = 'customerid';
        $this->mViewableSearchResultKeys[] = 'projectid';
        $this->mViewableSearchResultKeys[] = 'accountmanager';
        $this->mViewableSearchResultKeys[] = 'subscriptiondate';
        $this->mViewableSearchResultKeys[] = 'expirationdate';
        $this->mViewableSearchResultKeys[] = 'contractvalue';

        $this->mSearchOrderBy = 'expirationdate,number,description';
    }

    public function doCreate(
        $params,
        $userId
        )
    {
        $result = false;

        if (
            !isset($params['projectid'] )
            or !strlen( $params['projectid'] )
            ) $params['projectid'] = '0';

        if (
            !isset($params['customerid'] )
            or !strlen( $params['customerid'] )
            ) $params['customerid'] = '0';

        $params['trashed'] = $this->mrDomainDA->fmtfalse;

        if ( count( $params ) ) {


            $item_id = $this->mrDomainDA->getNextSequenceValue( $this->mTable.'_id_seq' );

            $key_pre = $value_pre = $keys = $values = '';

            require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php');
            $country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );

            if ( !isset($params['done'] ) ) $params['done'] = false;

            while ( list( $key, $val ) = each( $params ) ) {
                $key_pre = ',';
                $value_pre = ',';

                switch ( $key ) {
                case 'contractvalue':
                    $val = str_replace( ',', '.', $val );
                    $val = number_format(
                        $val,
                        $country->FractDigits(),
                        '.',
                        ''
                        );

                case 'description':
                case 'trashed':
                case 'accountmanager':
                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$this->mrDomainDA->formatText( $val );
                    break;

                case 'customerid':
                case 'projectid':
                case 'periodicity':
                case 'number':
                    if ( !strlen( $key ) ) $key = 0;
                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$val;
                    break;

                case 'subscriptiondate':
                case 'expirationdate':
                    $val = $this->mrDomainDA->getTimestampFromDateArray( $val );

                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$this->mrDomainDA->formatText( $val );
                    break;

                case 'done':
                    $keys .= $key_pre.$key;
                    $values .= $value_pre.$this->mrDomainDA->formatText(
                        $val ?
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmttrue :
                        \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmtfalse
                        );
                    break;

                default:
                    break;
                }
            }

            if ( strlen( $values ) ) {
                if ( $this->mrDomainDA->Execute( 'INSERT INTO '.$this->mTable.' '.
                                               '(id,ownerid'.$keys.') '.
                                               'VALUES ('.$item_id.','.
                                               $userId.
                                               $values.')' ) )
                {
                    $result = $item_id;

                    $this->setLastContractNumber( $params['number'] );
                }
            }
        }

        return $result;
    }

    public function doEdit($params)
    {
        $result = false;

        if ( $this->mItemId ) {
            if ( count( $params ) ) {
                $start = 1;
                $update_str = '';

                require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php');
                $country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );

                while ( list( $field, $value ) = each( $params ) ) {
                    if ( $field != 'id' ) {
                        switch ( $field ) {
                        case 'contractvalue':
                            $value = str_replace( ',', '.', $value );
                            $value = number_format(
                                $value,
                                $country->FractDigits(),
                                '.',
                                ''
                                );

                        case 'description':
                        case 'trashed':
                        case 'accountmanager':
                            if ( !$start ) $update_str .= ',';
                            $update_str .= $field.'='.$this->mrDomainDA->formatText( $value );
                            $start = 0;
                            break;

                        case 'customerid':
                        case 'projectid':
                        case 'periodicity':
                        case 'number':
                            if ( !strlen( $value ) ) $value = 0;
                            if ( !$start ) $update_str .= ',';
                            $update_str .= $field.'='.$value;
                            $start = 0;
                            break;

                        case 'subscriptiondate':
                        case 'expirationdate':
                            $value = $this->mrDomainDA->getTimestampFromDateArray( $value );

                            if ( !$start ) $update_str .= ',';
                            $update_str .= $field.'='.$this->mrDomainDA->formatText( $value );
                            $start = 0;
                            break;

                        case 'done':
                            if ( !$start ) $update_str .= ',';
                            $update_str .= $field.'='.$this->mrDomainDA->formatText(
                                $value ?
                                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmttrue :
                                \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->fmtfalse
                                );
                            $start = 0;
                            break;

                        default:
                            break;
                        }
                    }
                }

                $query = $this->mrDomainDA->Execute(
                    'UPDATE '.$this->mTable.' '.
                    'SET '.$update_str.' '.
                    'WHERE id='.$this->mItemId );

                if ( $query ) $result = true;
            }
        }

        return $result;
    }

    public function doRemove($userId)
    {
        $result = FALSE;

        $result = $this->mrDomainDA->Execute(
            'DELETE FROM '.$this->mTable.' '.
            'WHERE id='.$this->mItemId );

        return $result;
    }

    public function doGetItem($userId)
    {
        $result = FALSE;

        $item_query = $this->mrDomainDA->Execute(
            'SELECT * '.
            'FROM '.$this->mTable.' '.
            'WHERE id='.$this->mItemId );

        if (
            is_object( $item_query )
            and $item_query->getNumberRows()
            )
        {
            $result = $item_query->getFields();
        }

        return $result;
    }

    public function doTrash($arg)
    {
        return true;
    }

    public function doGetSummary()
    {
        $result = '';

        /*
        $fax_list = $this->getFaxList();

        $result =
'<grid>
  <children>';

        $row = 0;

        require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php');
        $country = new LocaleCountry(
            \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry()
            );

        foreach ( $fax_list as $fax ) {
            $caller = $fax['companyname'] ? $fax['companyname'] : $fax['callerid'];
            if ( !strlen( $caller ) ) $caller = 'Unknown';

            $result .=
'  <label row="'.$row.'" col="0">
    <args>
      <label>- </label>
      <compact>true</compact>
    </args>
  </label>
  <link row="'.$row.'" col="1">
    <args>
      <label type="encoded">'.urlencode( $country->FormatShortArrayDate( $fax['date'] ).' - '.$caller ).'</label>
      <compact>true</compact>
      <target>_blank</target>
      <link type="encoded">'.urlencode(
        WuiEventsCall::buildEventsCallString(
            'innoworkfax.php',
            array(
                array(
                    'view',
                    'showfax',
                    array(
                        'faxfile' => $fax['filename']
                        )
                    )
                )
            )
        ).'</link>
    </args>
  </link>';
            $row++;
        }

        $result .=
'  </children>
</grid>';

        */

        return $result;
    }

    public function setLastContractNumber($number)
    {
        require_once('innomatic/domain/DomainSettings.php');

        $domain_settings = new DomainSettings( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess() );
        $domain_settings->setKey(
            'innoworkcontracts-lastcontractnumber',
            $number
            );

        return true;
    }

    public function getLastContractNumber()
    {
        require_once('innomatic/domain/DomainSettings.php');

        $domain_settings = new DomainSettings( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess() );
        $result = $domain_settings->getKey( 'innoworkcontracts-lastcontractnumber' );

        if ( !strlen( $result ) ) $result = 0;

        return $result;
    }

    public function createExpiration($params)
    {
       $result = FALSE;

           if ( count( $params ) ) {
                $id = $this->mrDomainDA->getNextSequenceValue( 'innowork_contracts_expirations_id_seq' );
                require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php');
                $country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );


                $key_pre = $value_pre = $keys = $values = '';

                while ( list( $key, $val ) = each( $params ) ) {
                    $key_pre = ',';
                    $value_pre = ',';

                    switch ( $key ) {
                    case 'description':
                    case 'amount':

                        $keys .= $key_pre.$key;
                        $values .= $value_pre.$this->mrDomainDA->formatText( $val );
                        break;

                    case 'expirationdate':
                        $val = $this->mrDomainDA->getTimestampFromDateArray( $val );

                        $keys .= $key_pre.$key;
                        $values .= $value_pre.$this->mrDomainDA->formatText( $val );
                        break;

                    }
                }

                $contract_data = $this->getItem();

                require_once('innowork/groupware/InnoworkEvent.php');
                $innowork_event = new InnoworkEvent(
                    $this->mrRootDb,
                       $this->mrDomainDA
                    );

                $innowork_event->Create( array(
                        'startdate' => $params['expirationdate'],
                        'enddate' => $params['expirationdate'],
                        'description' => $params['description'],
                        'companyid' => $contract_data['customerid'],
                        'ownerid' => $contract_data['ownerid'],
                        'notes' => $contract_data['description'],
                        'exttype' => 'contract',
                        'extid' => $this->mItemId,
                        'exticon' => 'history',
                        'extdata' => $id
                        ) );
                $innowork_event->mAcl->CopyAcl( 'contract', $this->mItemId );

                if ( strlen( $values ) ) {
                    if ( $this->mrDomainDA->Execute( 'INSERT INTO innowork_contracts_expirations '.
                                                '(id,contractid,eventid'.$keys.') '.
                                                'VALUES ('.$id.','.$this->mItemId.','.$innowork_event->mItemId.
                                                $values.')' ) ) $result = $id;
                }


            }

        return $result;
    }

    public function removeExpiration($id)
    {
        $result = FALSE;

        $query = $this->mrDomainDA->Execute( 'SELECT * FROM innowork_contracts_expirations '.
                                    'WHERE id='.$id );

        require_once('innowork/groupware/InnoworkEventFactory.php');
        $fact = new InnoworkEventFactory();

        $fact->RemoveExternalEvent('contract',$this->mItemId,$id);
        $result = $this->mrDomainDA->Execute( 'DELETE FROM innowork_contracts_expirations '.
                                               'WHERE id='.$id );

                                               /*
        $delete_query = $this->mrDomainDA->Execute( 'DELETE FROM innowork_calendar '.
                                       'WHERE id='.$query->getFields('eventid') );

        if ( $delete_query  ) {
            $result = $this->mrDomainDA->Execute( 'DELETE FROM innowork_contracts_expirations '.
                                               'WHERE id='.$id );
        }
        */

        return $result;
    }

    public function editExpiration(
        $params, $id
        )
    {
        $result = false;

            if ( count( $params ) ) {
                $start = 1;
                $update_str = '';

                require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php');
                $country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentUser()->getCountry() );

                while ( list( $field, $value ) = each( $params ) ) {
                        switch ( $field ) {
                        case 'description':
                        case 'amount':

                            if ( !$start ) $update_str .= ',';
                            $update_str .= $field.'='.$this->mrDomainDA->formatText( $value );
                            $start = 0;
                            break;

                        case 'expirationdate':
                            $value = $this->mrDomainDA->getTimestampFromDateArray( $value );

                            if ( !$start ) $update_str .= ',';
                            $update_str .= $field.'='.$this->mrDomainDA->formatText( $value );
                            $start = 0;
                            break;

                        default:
                            break;
                    }
                }
                $contract_data = $this->getItem();

                $query = $this->mrDomainDA->Execute(
                    'UPDATE innowork_contracts_expirations '.
                    'SET '.$update_str.' '.
                    'WHERE id='.$id );

                $exp_data = $this->getExpirationData( $id );

                require_once('innowork/groupware/InnoworkEvent.php');

                $control_query = $this->mrDomainDA->Execute( 'SELECT * FROM innowork_calendar '.
                                'WHERE id='.$exp_data['eventid'] );

                if( $control_query->getNumberRows()!= 0) {

                $innowork_event = new InnoworkEvent(
                    $this->mrRootDb,
                       $this->mrDomainDA,
                       $exp_data['eventid']
                    );

                $innowork_event->Edit ( array(
                        'startdate' => $params['expirationdate'],
                        'enddate' => $params['expirationdate'],
                        'description' => $params['description'],
                        'companyid' => $contract_data['customerid'],
                        'ownerid' => $exp_data['ownerid'],
                        'exttype' => 'contract',
                        'extid' => $this->mItemId,
                        'exticon' => 'history'
                        ) );
                }else {
                        $innowork_event = new InnoworkEvent(
                            $this->mrRootDb,
                               $this->mrDomainDA
                            );

                        $innowork_event->Create ( array(
                                'startdate' => $params['expirationdate'],
                                'enddate' => $params['expirationdate'],
                                'description' => $params['description'],
                                'companyid' => $contract_data['customerid'],
                                'ownerid' => $exp_data['ownerid'],
                                'exttype' => 'contract',
                                'extid' => $this->mItemId,
                                'exticon' => 'history',
                                'extdata' => $id
                                ) );
                        $innowork_event->mAcl->CopyAcl( 'contract', $this->mItemId );

                    }
                if ( $query ) $result = TRUE;
            }

        return $result;
    }

    public function getExpirationList($orderField = FALSE)
    {
        $result = FALSE;

        $query = $this->mrDomainDA->Execute( 'SELECT * '.
                                            'FROM innowork_contracts_expirations '.
                                            'WHERE contractid='.$this->mItemId.' '.
                                            ( $orderField ? ' ORDER BY '.$orderField : ' ORDER BY expirationdate ASC' ) );

        if ( is_object( $query ) and $query->getNumberRows() ) {
            while ( !$query->eof ) {
                $result[] = $query->getFields();
                $query->moveNext();
            }
        }

        return $result;
    }

    public function getExpirationData($id)
    {
        $result = FALSE;

        $query = $this->mrDomainDA->Execute( 'SELECT * '.
                                            'FROM innowork_contracts_expirations  '.
                                            'WHERE id='.$id );

        if ( is_object( $query ) and $query->getNumberRows() ) {
            if ( $field ) $result = $query->getFields( $field );
            else $result = $query->getFields();
        }

        return $result;
    }

    public function getTotalAmount()
    {
        $result = array();

            require_once('innomatic/locale/LocaleCatalog.php'); require_once('innomatic/locale/LocaleCountry.php');
            $locale_country = new LocaleCountry( \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getCountry() );

            $result['amount'] = 0;


            $rows_query = \Innomatic\Core\InnomaticContainer::instance('\Innomatic\Core\InnomaticContainer')->getCurrentDomain()->getDataAccess()->Execute(
                'SELECT amount '.
                'FROM innowork_contracts_expirations '.
                'WHERE contractid='.$this->mItemId
                );

            while ( !$rows_query->eof ) {
                $result['amount'] +=  ( $rows_query->getFields( 'amount' )  );

                $rows_query->moveNext();
            }

            $result['amount'] = number_format(
                $result['amount'],
                $locale_country->FractDigits(),
                '.',
                ''
                );

        return $result;

    }
}

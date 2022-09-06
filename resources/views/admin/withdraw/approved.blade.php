@extends('layouts.admin')
@section('content')

<div class="card">
    <div class="card-header">
        Approved {{ trans('global.withdraw.title_singular') }}
    </div>

    <div class="card-body">
        @if($errors->has('validasiBank'))
            {{-- <em class="invalid-feedback">
                {{dd( $errors->first('validasiBank') )}}
            </em> --}}
                <div class="alert alert-danger" role="alert">
                    {{$errors->first('validasiBank') }}
              </div>
        @endif
        <form action="{{ route("admin.withdraw.approvedprocess") }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')  

            <div class="form-group {{ $errors->has('acc_pay') ? 'has-error' : '' }}">
                <label for="acc_pay">{{ trans('global.withdraw.fields.acc_pay') }}*</label>
                <select name="acc_pay" class="form-control" required>
                    <option value="">-- choose account --</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}"{{ old('code') == $account->id ? ' selected' : '' }}>
                        {{ $account->code }}-{{ $account->name }} {{ $account->last_name }}
                        </option>
                    @endforeach
                </select>
                <div class="form-group select required beneficiary_account_validation_form_bank">
                    <label class="control-label select required" for="beneficiary_account_validation_form_bank">Bank <abbr title="required">*</abbr></label>
                    <select class="form-control select required js-beneficiary-bank-select select2" data-placeholder="Select beneficiary" data-allow-clear="true" required="required" aria-required="true" name="bank" id="beneficiary_account_validation_form_bank"><option value=""></option>
                        {{-- <option value="">-- choose account --</option> --}}
                        <option value="aceh">PT. BANK ACEH</option>
                        <option value="aceh_syar">PT. BPD ISTIMEWA ACEH SYARIAH</option>
                        <option value="agris">PT. BANK AGRIS</option>
                        <option value="agroniaga">PT. BANK RAKYAT INDONESIA AGRONIAGA TBK.</option>
                        <option value="amar">PT. BANK AMAR INDONESIA</option>
                        <option value="andara">PT. BANK ANDARA</option>
                        <option value="anglomas">PT. ANGLOMAS INTERNATIONAL BANK</option>
                        <option value="antar_daerah">PT. BANK ANTAR DAERAH</option>
                        <option value="anz">PT. BANK ANZ INDONESIA</option>
                        <option value="artajasa">PT. ARTAJASA PEMBAYARAN ELEKTRONIK</option>
                        <option value="artha">PT. BANK ARTHA GRAHA INTERNASIONAL TBK.</option>
                        <option value="artos">PT. BANK ARTOS INDONESIA</option>
                        <option value="bali">PT. BANK PEMBANGUNAN DAERAH BALI</option>
                        <option value="bangkok">BANGKOK BANK PUBLIC CO.LTD</option>
                        <option value="banten">PT. BANK BANTEN</option>
                        <option value="barclays">PT BANK BARCLAYS INDONESIA</option>
                        <option value="bca">PT. BANK CENTRAL ASIA TBK.</option>
                        <option value="bca_syar">PT. BANK BCA SYARIAH</option>
                        <option value="bengkulu">PT. BPD BENGKULU</option>
                        <option value="bisnis">PT. BANK BISNIS INTERNASIONAL</option>
                        <option value="bjb">PT. BANK PEMBANGUNAN DAERAH JABAR DAN BANTEN</option>
                        <option value="bjb_syar">PT. BANK JABAR BANTEN SYARIAH</option>
                        <option value="bni">PT. BANK NEGARA INDONESIA (PERSERO)</option>
                        <option value="bni_syar">PT. BANK SYARIAH INDONESIA TBK. - BNIS</option>
                        <option value="bnp">PT. BANK NUSANTARA PARAHYANGAN</option>
                        <option value="bnp_paribas">PT. BANK BNP PARIBAS INDONESIA</option>
                        <option value="boa">BANK OF AMERICA NA</option>
                        <option value="bri">PT. BANK RAKYAT INDONESIA (PERSERO)</option>
                        <option value="bri_syar">PT. BANK SYARIAH INDONESIA TBK. - BRIS</option>
                        <option value="btn">PT. BANK TABUNGAN NEGARA (PERSERO)</option>
                        <option value="btn_syar">PT. BANK TABUNGAN NEGARA (PERSERO) UNIT USAHA SYARIAH</option>
                        <option value="btpn">PT. BANK TABUNGAN PENSIUNAN NASIONAL</option>
                        <option value="btpn_syar">PT. BANK TABUNGAN PENSIUNAN NASIONAL SYARIAH</option>
                        <option value="bukopin">PT BANK KB BUKOPIN TBK.</option>
                        <option value="bukopin_syar">PT. BANK SYARIAH BUKOPIN</option>
                        <option value="bumi_artha">PT. BANK BUMI ARTA</option>
                        <option value="bumiputera">PT. BANK BUMIPUTERA</option>
                        <option value="capital">PT. BANK CAPITAL INDONESIA</option>
                        <option value="centratama">PT. CENTRATAMA NASIONAL BANK</option>
                        <option value="chase">KC JPMORGAN CHASE BANK, N.A.</option>
                        <option value="china">KC BANK OF CHINA LIMITED</option>
                        <option value="china_cons">PT. BANK CHINA CONSTRUCTION BANK INDONESIA, TBK.</option>
                        <option value="chinatrust">PT. BANK CTBC INDONESIA</option>
                        <option value="cimb">PT. BANK CIMB NIAGA TBK.</option>
                        <option value="cimb_rekening_ponsel">PT. BANK CIMB NIAGA TBK. - REKENING PONSEL</option>
                        <option value="cimb_syar">PT. BANK CIMB NIAGA TBK. - UNIT USAHA SYARIAH</option>
                        <option value="citibank">CITIBANK</option>
                        <option value="commonwealth">PT. BANK COMMONWEALTH</option>
                        <option value="danamon">PT. BANK DANAMON INDONESIA TBK.</option>
                        <option value="danamon_syar">PT. BANK DANAMON INDONESIA UNIT USAHA SYARIAH</option>
                        <option value="dbs">PT. BANK DBS INDONESIA</option>
                        <option value="deutsche">DEUTSCHE BANK AG.</option>
                        <option value="dinar">PT. BANK DINAR INDONESIA</option>
                        <option value="dipo">PT. BANK DIPO INTERNATIONAL</option>
                        <option value="diy">PT. BANK PEMBANGUNAN DAERAH DIY</option>
                        <option value="diy_syar">PT. BANK PEMBANGUNAN DAERAH DIY UNIT USAHA SYARIAH</option>
                        <option value="dki">PT. BANK DKI</option>
                        <option value="dki_syar">PT. BANK DKI UNIT USAHA SYARIAH</option>
                        <option value="ekonomi">PT. BANK EKONOMI RAHARJA</option>
                        <option value="fama">PT. BANK FAMA INTERNATIONAL</option>
                        <option value="ganesha">PT. BANK GANESHA</option>
                        <option value="hana">PT. BANK KEB HANA INDONESIA</option>
                        <option value="harda">PT. BANK HARDA INTERNATIONAL</option>
                        <option value="hs_1906">PT. BANK HS 1906</option>
                        <option value="hsbc">PT. BANK HSBC INDONESIA</option>
                        <option value="icbc">PT. BANK ICBC INDONESIA</option>
                        <option value="ina_perdana">PT. BANK INA PERDANA</option>
                        <option value="index_selindo">PT. BANK INDEX SELINDO</option>
                        <option value="india">PT. BANK OF INDIA INDONESIA TBK.</option>
                        <option value="jambi">PT. BANK PEMBANGUNAN DAERAH JAMBI</option>
                        <option value="jasa_jakarta">PT. BANK JASA JAKARTA</option>
                        <option value="jateng">PT. BANK PEMBANGUNAN DAERAH JAWA TENGAH</option>
                        <option value="jateng_syar">PT. BPD JAWA TENGAH UNIT USAHA SYARIAH</option>
                        <option value="jatim">PT. BANK PEMBANGUNAN DAERAH JATIM</option>
                        <option value="jatim_syar">PT. BANK PEMBANGUNAN DAERAH JATIM - UNIT USAHA SYARIAH</option>
                        <option value="jtrust">PT. BANK JTRUST INDONESIA TBK.</option>
                        <option value="kalbar">PT. BANK PEMBANGUNAN DAERAH KALBAR</option>
                        <option value="kalbar_syar">PT. BANK PEMBANGUNAN DAERAH KALBAR - UNIT USAHA SYARIAH</option>
                        <option value="kalsel">PT. BANK PEMBANGUNAN DAERAH KALSEL</option>
                        <option value="kalsel_syar">PT. BANK PEMBANGUNAN DAERAH KALSEL - UNIT USAHA SYARIAH</option>
                        <option value="kalteng">PT. BPD KALIMANTAN TENGAH</option>
                        <option value="kaltim">PT. BANK PEMBANGUNAN DAERAH KALTIM</option>
                        <option value="kaltim_syar">PT. BANK PEMBANGUNAN DAERAH KALTIM - UNIT USAHA SYARIAH</option>
                        <option value="kesejahteraan">PT. BANK KESEJAHTERAAN EKONOMI</option>
                        <option value="lampung">PT. BANK PEMBANGUNAN DAERAH LAMPUNG</option>
                        <option value="maluku">PT. BANK PEMBANGUNAN DAERAH MALUKU</option>
                        <option value="mandiri">PT. BANK MANDIRI (PERSERO) TBK.</option>
                        <option value="mandiri_syar">PT. BANK SYARIAH INDONESIA TBK. - MANDIRI</option>
                        <option value="mandiri_taspen">PT. BANK MANDIRI TASPEN POS</option>
                        <option value="maspion">PT. BANK MASPION INDONESIA</option>
                        <option value="mayapada">PT. BANK MAYAPADA INTERNASIONAL, TBK</option>
                        <option value="maybank">PT. BANK MAYBANK INDONESIA TBK.</option>
                        <option value="maybank_syar">PT. BANK MAYBANK SYARIAH INDONESIA</option>
                        <option value="maybank_uus">PT. BANK MAYBANK INDONESIA TBK. UNIT USAHA SYARIAH</option>
                        <option value="mayora">PT. BANK MAYORA</option>
                        <option value="mega_syar">PT. BANK MEGA SYARIAH</option>
                        <option value="mega_tbk">PT. BANK MEGA TBK.</option>
                        <option value="mestika">PT. BANK MESTIKA DHARMA</option>
                        <option value="metro">PT. BANK METRO EXPRESS</option>
                        <option value="mitraniaga">PT. BANK MITRANIAGA</option>
                        <option value="mitsubishi">THE BANK OF TOKYO MITSUBISHI UFJ LTD.</option>
                        <option value="mizuho">PT. BANK MIZUHO INDONESIA</option>
                        <option value="mnc">PT. BANK MNC INTERNASIONAL TBK.</option>
                        <option value="muamalat">PT. BANK MUAMALAT INDONESIA</option>
                        <option value="multiarta">PT. BANK MULTI ARTA SENTOSA</option>
                        <option value="mutiara">PT. BANK MUTIARA TBK.</option>
                        <option value="nagari">PT. BANK NAGARI</option>
                        <option value="niaga_syar">PT. BANK NIAGA TBK. SYARIAH</option>
                        <option value="nobu">PT. BANK NATIONALNOBU</option>
                        <option value="ntb">PT. BANK PEMBANGUNAN DAERAH NTB</option>
                        <option value="ntt">PT. BANK PEMBANGUNAN DAERAH NTT</option>
                        <option value="ocbc">PT. BANK OCBC NISP TBK.</option>
                        <option value="ocbc_syar">PT. BANK OCBC NISP TBK. - UNIT USAHA SYARIAH</option>
                        <option value="panin">PT. PANIN BANK TBK.</option>
                        <option value="panin_syar">PT. BANK PANIN SYARIAH</option>
                        <option value="papua">PT. BANK PEMBANGUNAN DAERAH PAPUA</option>
                        <option value="permata">PT. BANK PERMATA TBK.</option>
                        <option value="permata_syar">PT. BANK PERMATA TBK. UNIT USAHA SYARIAH</option>
                        <option value="prima_master">PT. PRIMA MASTER BANK</option>
                        <option value="pundi">PT. BANK PUNDI INDONESIA</option>
                        <option value="purba">PT. BANK PURBA DANARTA</option>
                        <option value="qnb">PT. BANK QNB INDONESIA TBK.</option>
                        <option value="rabobank">PT. BANK RABOBANK INTERNATIONAL INDONESIA</option>
                        <option value="rbos">THE ROYAL BANK OF SCOTLAND N.V.</option>
                        <option value="resona">PT. BANK RESONA PERDANIA</option>
                        <option value="riau">PT. BANK PEMBANGUNAN DAERAH RIAU KEPRI</option>
                        <option value="royal">PT. BANK DIGITAL BCA</option>
                        <option value="sampoerna">PT. BANK SAHABAT SAMPOERNA</option>
                        <option value="sbi">PT. BANK SBI INDONESIA</option>
                        <option value="shinhan">PT. BANK SHINHAN INDONESIA</option>
                        <option value="sinarmas">PT. BANK SINARMAS</option>
                        <option value="sinarmas_syar">PT. BANK SINARMAS UNIT USAHA SYARIAH</option>
                        <option value="stanchard">STANDARD CHARTERED BANK</option>
                        <option value="sulselbar">PT. BANK SULSELBAR</option>
                        <option value="sulselbar_syar">PT. BPD SULAWESI SELATAN DAN SULAWESI BARAT UNIT USAHA SYARIAH</option>
                        <option value="sulteng">PT. BPD SULAWESI TENGAH</option>
                        <option value="sultenggara">PT. BPD SULAWESI TENGGARA</option>
                        <option value="sulut">PT. BANK SULUT</option>
                        <option value="sumbar">PT. BPD SUMATERA BARAT</option>
                        <option value="sumitomo">PT. BANK SUMITOMO MITSUI INDONESIA</option>
                        <option value="sumsel_babel">PT. BPD SUMSEL DAN BABEL</option>
                        <option value="sumsel_babel_syar">PT. BPD SUMSEL DAN BABEL UNIT USAHA SYARIAH</option>
                        <option value="sumut">PT. BANK PEMBANGUNAN DAERAH SUMUT</option>
                        <option value="sumut_syar">PT. BANK PEMBANGUNAN DAERAH SUMUT UUS</option>
                        <option value="uob">PT. BANK UOB INDONESIA</option>
                        <option value="victoria">PT. BANK VICTORIA INTERNATIONAL</option>
                        <option value="victoria_syar">PT. BANK VICTORIA SYARIAH</option>
                        <option value="woori">PT. BANK WOORI SAUDARA INDONESIA 1906 TBK.</option>
                        <option value="yudha_bhakti">PT. BANK YUDHA BHAKTI</option>
                        <option value="bank_indonesia">BANK INDONESIA</option>
                        <option value="gopay">GO-PAY</option>
                        <option value="ovo">OVO</option>
                        <option value="riau_syar">PT. BANK PEMBANGUNAN DAERAH RIAU KEPRI SYARIAH</option>
                        <option value="jago">PT. BANK JAGO TBK.</option>
                        <option value="bsi">PT. BANK SYARIAH INDONESIA, TBK.</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="account">Account *</label>
                    <input type="text" id="account" class="form-control" required name="account" value="{{ $withdraw->bank_acc_no }}">
                </div>


                @if($errors->has('acc_pay'))
                    <em class="invalid-feedback">
                        {{ $errors->first('acc_pay') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.withdraw.fields.acc_pay_helper') }}
                </p>
            </div> 
                     
            <div class="form-group {{ $errors->has('status') ? 'has-error' : '' }}">
            <div class="checkbox">
            <label>Approved {{ trans('global.withdraw.fields.status') }}*</label>
            <input type="checkbox" data-toggle="toggle" name="status" id="status">    
            </div>
                @if($errors->has('status'))
                    <em class="invalid-feedback">
                        {{ $errors->first('status') }}
                    </em>
                @endif
                <p class="helper-block">
                    {{ trans('global.withdraw.fields.status_helper') }}
                </p>
                <input type="hidden" id="id" name="id" value="{{ $withdraw->id }}">
            </div>
            <div>
                <input class="btn btn-danger" type="submit" value="Approve">
            </div>
        </form>


    </div>
</div>
@endsection

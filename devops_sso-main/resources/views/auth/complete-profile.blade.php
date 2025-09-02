@extends('layouts.app')

@section('content')
<div class="container">
  <h3>สมัครผู้ใช้ (เชื่อม i‑Industry)</h3>

  {{-- guard: show error if token/snap missing --}}
  @if(empty($token) || empty($snap))
    <div class="alert alert-danger">ลิงก์ไม่ถูกต้องหรือหมดอายุ</div>
  @else
  <form method="POST" action="{{ route('register.complete-profile.store') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    {{-- read-only facts from i-Industry --}}
    <div class="form-group mb-3">
      <label class="form-label">เลขอ้างอิง (Tax Number)</label>
      <input class="form-control" value="{{ $snap['tax_number'] ?? '' }}" readonly>
    </div>

    <div class="form-group mb-3">
      <label class="form-label">ประเภทผู้ใช้ (Juristic Type)</label>
      <input class="form-control" value="{{ $snap['juristic_status'] ?? $snap['jt'] ?? '' }}" readonly>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group mb-3">
          <label class="form-label">ชื่อ</label>
          <input class="form-control" value="{{ $snap['iCustomer']['UserFirstName'] ?? '' }}" readonly>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group mb-3">
          <label class="form-label">สกุล</label>
          <input class="form-control" value="{{ $snap['iCustomer']['UserLastName'] ?? '' }}" readonly>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="form-group mb-3">
          <label class="form-label">อีเมล</label>
          <input class="form-control" value="{{ $snap['iCustomer']['UserEmail'] ?? '' }}" readonly>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group mb-3">
          <label class="form-label">โทรศัพท์</label>
          <input class="form-control" value="{{ $snap['iCustomer']['UserPhone'] ?? '' }}" readonly>
        </div>
      </div>
    </div>

    {{-- extra inputs user can add (optional) --}}
    {{--
    <div class="form-group mb-3">
      <label class="form-label">ที่อยู่</label>
      <input class="form-control" name="address" value="{{ old('address') }}">
    </div>
    --}}

    {{-- show validation errors (from controller) --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <button class="btn btn-primary" type="submit">ยืนยันสมัครและเข้าใช้งาน</button>
  </form>
  @endif
</div>
@endsection

@if ($errors->any())
    <?php
    $errorsToDisplay = $errors->all();
    $errorsToDisplay = array_unique($errorsToDisplay);
    ?>
    <div class="alert alert-danger">
        Vui lòng sửa các lỗi sau trước khi chuyển tiếp.
        <ul>
            @foreach ($errorsToDisplay as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@if(Session::has('message'))
    <div id="closeAlert" class="alert alert-box alert-success">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ Session::get('message') }}
        <?php Session::forget('message'); ?>
    </div>
@endif
@if(Session::has('error_message'))
    <div id="closeAlert" class="alert alert-box alert-danger">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        {{ Session::get('error_message') }}
        <?php Session::forget('error_message'); ?>
    </div>
@endif
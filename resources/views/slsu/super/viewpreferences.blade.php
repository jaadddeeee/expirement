<form id = "frmPreferenceValue">
  <input type = "hidden" name = "campus" value = "{{Crypt::encryptstring($Campus)}}">
  <div class="table-responsive text-nowrap">
    <table class="table table-borderless table-sm table-hover">
      <thead>
        <tr>
          <!-- <th style = "width: 250px">Preference Name</th> -->
          <th style = "width: 250px">Preference Name</th>
          <th>Preference Value</th>

        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @foreach($prefs as $pref)
        <tr>
            <input type = "hidden" name = "ids[]" value = "{{Crypt::encryptstring($pref->id)}}">
            <td><label for="pref-{{$pref->id}}">{{$pref->DefaultName}}</label></td>
            <td><input type = "text" id = "pref-{{$pref->id}}" name = "pref-{{$pref->id}}" class = "form-control" value = "{{$pref->DefaultValue}}"></td>
        </tr>
        @endforeach
        <tr>
            <td colspan = "2"><button id = "btnSavePreferences" class = "ml-1 btn btn-primary">Save Changes</button></td>
        </tr>
      </tbody>
    </table>

  </div>
</form>

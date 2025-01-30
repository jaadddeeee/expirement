<div class="modal fade" id="modalDORMG" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel1">DORMITORY</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form id = "frmAdd">
            @csrf
            <div id = "msg"></div>
            <div class="row">
              <div class="col mb-3">
                <label for="nameBasic" class="form-label">Search Name / Student:</label>
                <input type="text" id="str" name = "str" class="typeahead form-control" placeholder="Enter Name / Student" autofocus>
              </div>
            </div>
            <div class="row g-2">
              <div class="col mb-3">
                <label for="Description" class="form-label">Reason:</label>
                <textarea id="Description" name = "Description" class="form-control" placeholder="Reason"></textarea>
              </div>
            </div>

            <div class="row g-2">
              <div class="col mb-0">
                <label for="Description" class="form-label">School Year:</label>
                <select class = "form-select" name = "SchoolYear" id = "SchoolYear">
                    <option value="0"></option>
                    @foreach(GENERAL::SchoolYears() as $index => $sy)
                      <option value="{{$sy}}">{{$sy}}</option>
                    @endforeach
                </select>
              </div>

              <div class="col mb-0">
                <label for="Description" class="form-label">Semester:</label>
                <select class = "form-select" name = "Semester" id = "Semester">
                    <option value="0"></option>
                    @foreach(GENERAL::Semesters() as $index => $sem)
                      <option value="{{$index}}">{{$sem['Long']}}</option>
                    @endforeach
                </select>
              </div>

            </div>
          </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" id = "btnAddReason" class="btn btn-primary">Add to list</button>
      </div>
    </div>
  </div>
</div>



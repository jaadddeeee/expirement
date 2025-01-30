<div class="modal fade" id="modalSearch" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-top modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
          <form id = "frmGlobalSearch">
            @csrf
            <div id = "globalmsg"></div>
            <div class="row">
                <div class="col-lg-12 col-sm-12">
                    <div class="input-group mb-3">
                      <span class="input-group-text bg-primary text-white">Search</span>
                      <input type="text" class="form-control" placeholder = "Enter Student Number / Name" name = "gsearch" id = "gsearch">
                      <span class="globalsearch input-group-text"><i class = "fa fa-search"></i></span>
                    </div>
                </div>
            </div>
          </form>
      </div>
    </div>
  </div>
</div>


<script>
$("#modalSearch").on('shown.bs.modal', function(){
    $("#gsearch").focus();
})
</script>

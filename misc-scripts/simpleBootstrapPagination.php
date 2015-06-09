<?php
function makePagination($resultCount=0, $limit=50, $page=0, $total=0, $url='', $showExport=false, $exportUrl='/scripts/getTransactionCSV'){

    $html = '<br/>';
    $html .= '<div>';

    if($total > $limit)
    {
        if($page == 0)
            $current = $resultCount;
        else
            $current = ($page*$limit) + $resultCount;

        $html .= '<div>Showing <strong>'.(($page*$limit)+1).'</strong> to <strong>'.$current.'</strong> of <strong>'.$total.'</strong> results.</div><br/>';

        $prev = false;
        $next = false;

        if($current > $limit)
            $prev = true;

        if($total > $current)
            $next = true;

        $html .= '<div class="btn-group" role="group" aria-label="pagination">';
        $html .= '<a href="'.$url.($page-1).'" type="button" class="btn btn-primary btn-sm" '.($prev?'':'disabled="disabled"').'><i class="fa fa-long-arrow-left fa-lg"></i> Previous '.$limit.'</a>';
        $html .= '<a href="'.$url.($page+1).'" type="button" class="btn btn-primary btn-sm" '.($next?'':'disabled="disabled"').'>Next '.$limit.' <i class="fa fa-long-arrow-right fa-lg"></i></a>';
        $html .= '</div>';
        $html .= '&nbsp;<a href="'.$url.'" type="button" class="btn btn-primary btn-sm" '.(($page==0)?'disabled="disabled"':'').'>First Page</a>';
        $html .= '&nbsp;<a href="'.$url.(floor($total / $limit)).'" type="button" class="btn btn-primary btn-sm" '.(($page==floor($total / $limit))?'disabled="disabled"':'').'>Last Page</a>';
    }
    else
        $html .= '<div>Showing <strong>'.$resultCount.'</strong> of <strong>'.$total.'</strong> results.</div><br/>';

    // Adding export csv button in here
    if($showExport)
        $html .= ' <a href="'.$exportUrl.'" type="button" class="btn btn-primary btn-sm" style="margin-right: 5px;">Save as CSV</a> <h6 style="display: inline-block;">Note: Large CSV dataset downloads (>50k) takes awhile.</h6>';
    $html .= '</div>';

    return $html;
}
?>
<?php defined('SYSPATH') or die('No direct script access.');

class PaginationArr {
    public function get_date_pagination($dates, $current, $base_url, $holder = '', $leave_out = 8, $per_page = 1){
        $total = count($dates);
        if ($current == 'last') {
            $current_index = $total - 1;
        } elseif ( ! is_numeric($current)) { // jako argument zostal podany jeden z kluczy tablicy
            for ($i = 0; $i < $total; $i++) {
                if ($dates[$i][0] == $current) break;
            }
            $current_index = $i == $total ? ($i-1) : $i;
        } else {
            $current_index = $current;
        }
        
        $html = $this->_build_dates_pagelinks( array( 'TOTAL_POSS'  => $total,
												'PER_PAGE'    => $per_page,
												'CUR_ST_VAL'  => $current_index,
												'L_SINGLE'    => '',
												'L_MULTI'     => 'strony',
												'BASE_URL'    => $base_url,
												'leave_out'   => $leave_out,
												'USE_ST'      => '{PG}',
												'DATES'       => $dates,
												'HOLDER'      => $holder,
												'CURRENT'     => $current
										)      );
        return array (
            'html' => $html,
            'current_index' => isset($dates[$current_index][0]) ? $dates[$current_index][0] : FALSE,
        );
        
		
    }
    
    private function _build_dates_pagelinks($data)	{
		$data['leave_out']    = isset($data['leave_out']) ? $data['leave_out'] : '';
		$data['USE_ST']		  = isset($data['USE_ST'])	? $data['USE_ST']	 : '';
		$work = array( 'pages' => 0, 'page_span' => '', 'st_dots' => '', 'end_dots' => '' );
		
		$section = !$data['leave_out'] ? 2 : $data['leave_out'];  
		$use_st  = !$data['USE_ST'] ? 'pg' : $data['USE_ST'];
		
		if ( $data['TOTAL_POSS'] > 0 )	{
			$work['pages'] = $data['TOTAL_POSS'];
		}
		
		$work['pages'] = $work['pages'] ? $work['pages'] : 1;
				
		$work['total_page'] = $work['pages'];
		$work['current_page'] = $data['CUR_ST_VAL']+1;
		
		//$first_url_declimer = strpos($data['BASE_URL'], '?') !== FALSE ? '&amp;' : '?';
		
		$previous_link = "";
		$next_link     = "";
		
		if ( $work['current_page'] > 1 ){
            $page = $data['CUR_ST_VAL'] - $data['PER_PAGE'];
            $previous_link = '<a rel="'.$data['HOLDER'].'" class="previous" href="'.str_replace($use_st, $data['DATES'][$page][0], $data['BASE_URL']).'" title="'.__('Previous').'">&lsaquo; '.__('previous').'</a>';
		} else {
            $previous_link = '<span class="previous-off">&laquo; '.__('previous').'</span>';
		}
		
		if ( $work['current_page'] < $work['pages'] && $work['current_page'] > 0 )	{
            $page = $data['CUR_ST_VAL'] + $data['PER_PAGE'];
            $next_link = '&nbsp;<a rel="'.$data['HOLDER'].'" class="next" href="'.str_replace($use_st, $data['DATES'][$page][0], $data['BASE_URL']).'" title="'.__('Next').'">'.__('next').' &rsaquo;</a>';
		} else {
            $next_link = '&nbsp;<span class="next-off">'.__('next').' &raquo;</a>';
		}
		
		if ($work['pages'] > 1)	{
            switch( ($work['pages'] - intval($work['pages'] / 10) * 10)) {
                case 2:
                case 3:
                case 4:
                    $pages_txt = 'strony';
                    break;
                default:
                    $pages_txt = 'stron';
            }
            
            $work['first_page'] = '';
			for ($i = 0; $i <= $work['pages'] - 1; ++$i ) {
				$realNo = $i * $data['PER_PAGE'];
                $pageNo = $i + 1;
                $pageNoDisplay = $data['DATES'][$i][1];
        
				if ($realNo == $data['CUR_ST_VAL'] && $data['CURRENT'] !== FALSE )	{
                    $work['page_span'] .= '&nbsp;<span class="active">'.$pageNoDisplay.'</span>';
				} else	{
					if ($pageNo < ($work['current_page'] - $section))	{
                        $work['st_dots'] = '<a rel="'.$data['HOLDER'].'" class="previous" href="'.str_replace($use_st, $data['DATES'][$realNo][0], $data['BASE_URL']).'" title="'.__('First').'">&laquo;</a>&nbsp;';
						continue;
					}
					
					if ($pageNo > ($work['current_page'] + $section)){
                        $work['end_dots'] = '&nbsp;<a rel="'.$data['HOLDER'].'" class="next" href="'.str_replace($use_st, $data['DATES'][(($work['pages']-1) * $data['PER_PAGE'])][0], $data['BASE_URL']).'" title="'.__('Last').'">&raquo;</a>&nbsp;';
                        break;
					}
					
					$work['page_span'] .= '&nbsp;<a rel="'.$data['HOLDER'].'" href="'.str_replace($use_st, $data['DATES'][$realNo][0], $data['BASE_URL']).'" title="'.$pageNoDisplay.'">'.$pageNoDisplay.'</a>';
				}
			}

			$work['return'] = $work['first_page'].$work['st_dots'].$previous_link.$work['page_span'].$next_link.$work['end_dots'];
			
		} else {
            if (empty($data['L_SINGLE']) && ! empty($data['DATES']) ) {
                $pageNoDisplay = current($data['DATES']);
                $realNo = $data['CUR_ST_VAL'];
                if ($data['CURRENT'] !== FALSE) {
                    $work['return'] = '<span class="active">'.$pageNoDisplay[1].'</span>';
                } else {
                    $work['return'] = '<a rel="'.$data['HOLDER'].'" href="'.str_replace($use_st, $data['DATES'][$realNo][0], $data['BASE_URL']).'" title="'.$pageNoDisplay[1].'">'.$pageNoDisplay[1].'</a>';
                }
            } else {
                $work['return'] = $data['L_SINGLE'];
            }
		}
        return $work['return'];
	}
}
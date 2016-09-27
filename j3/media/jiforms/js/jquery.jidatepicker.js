/*
 * @version     $Id: jquery.jidatepicker.js 060 2014-02-05 21:06:00Z Anton Wintergerst $
 * @package     JiDatePicker for jQuery
 * @copyright   Copyright (C) 2014 Jinfinity. All rights reserved.
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * @website     www.jinfinity.com
 * @email       support@jinfinity.com
 */
(function(jQuery){
    var JiDatePicker = function(element, options)
    {
        var self = this;

        this.setDefaultOptions = function() {
            this.element = element;
            this.container = null;
            this.daylabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            this.dayoffset = 1;
            this.monthlabels = ['January', 'February', 'March', 'April',
                'May', 'June', 'July', 'August', 'September',
                'October', 'November', 'December'];
            this.daysinmonth  = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            this.currentdate = new Date();

            this.daysselected = [];
            this.daysinactive = [];
            this.dayscustom = {};
            this.totalselectable = 0;
            this.inactivepast = true;
            this.rangepicker = false;
            this.rangemode = 'both';
            this.rangefrom = null;
            this.rangeto = null;
            this.rangepart = 'from';
            this.rangesize = 0;
            this.popup = true;
        };
        this.setDefaultOptions();

        // User Options
        if(options!=null) {
            jQuery.each(options, function(index, value) {
                self[index] = value;
            });
        }

        this.setPrivateOptions = function() {
            this.isopen = false;
        };
        this.setPrivateOptions();

        // Class Functions
        this.open = function(year, month, reset) {
            jQuery(this.element).trigger('willOpen', year, month);
            if(reset==null) reset = false;
            if(this.container==null || reset) {
                var classname = (this.popup)? ' popup' : ' inline';
                this.container = jQuery(document.createElement('div')).attr({
                    'class': 'jidatepicker'+classname
                }).css('display', 'block');
                jQuery(this.element).after(this.container);
            } else {
                jQuery(this.container).css('display', 'block');
            }
            var value = jQuery(this.element).val();
            if(value!=null && value.length>=4) {
                var result = this.stringToDate(value);
                year = result.year;
                month = result.month-1;
            }

            this.rebuild(year, month);
        };
        this.openPrev = function() {
            if(this.prevmonth==null) {
                var month = this.currentdate.getMonth();
                var year = this.currentdate.getFullYear();
                if(month==1) {
                    this.prevmonth = 11;
                    this.prevyear = year-1;
                } else {
                    this.prevmonth = month-1;
                    this.prevyear = year;
                }
            }
            this.rebuild(this.prevyear, this.prevmonth);
        };
        this.openNext = function() {
            if(this.nextmonth==null) {
                var month = this.currentdate.getMonth();
                var year = this.currentdate.getFullYear();
                if(month==11) {
                    this.nextmonth = 0;
                    this.nextyear = year+1;
                } else {
                    this.nextmonth = month+1;
                    this.nextyear = year;
                }
            }
            this.rebuild(this.nextyear, this.nextmonth);
        };
        this.rebuild = function(year, month) {
            if(month>11) month = 11;
            jQuery(this.container).children().remove();

            this.month = (isNaN(month) || month == null) ? this.currentdate.getMonth() : month;
            this.year  = (isNaN(year) || year == null) ? this.currentdate.getFullYear() : year;
            var firstdate = new Date(this.year, this.month, 1);
            this.startday = firstdate.getDay();
            this.monthlength = this.getMonthLength(this.month, this.year);

            var result = this.getPrevMonth(this.month, this.year);
            this.prevmonth = result.month;
            this.prevyear = result.year;
            this.prevmonthlength = this.getMonthLength(this.prevmonth, this.prevyear);
            result = this.getNextMonth(this.month, this.year);
            this.nextmonth = result.month;
            this.nextyear = result.year;
            this.nextmonthlength = this.getMonthLength(this.nextmonth, this.nextyear);

            this.render();
            //if(this.rangepicker) this.updateRange();
            jQuery(this.element).trigger('didRebuild', year, month);
        };
        this.getMonthLength = function(month, year) {
            var monthlength = this.daysinmonth[month];
            // Leap years
            if(month == 1) {
                if((year % 4 == 0 && year % 100 != 0) || year % 400 == 0){
                    monthlength = 29;
                }
            }
            return monthlength;
        };
        this.getPrevMonth = function(month, year) {
            if(month==0) {
                var prevmonth = 11;
                var prevyear = year-1;
            } else {
                var prevmonth = month-1;
                var prevyear = year;
            }
            return {
                month:prevmonth,
                year:prevyear
            };
        };
        this.getNextMonth = function(month, year) {
            year = parseInt(year);
            month = parseInt(month);
            if(month==11) {
                var nextmonth = 0;
                var nextyear = year+1;
            } else {
                var nextmonth = month+1;
                var nextyear = year;
            }
            return {
                month:nextmonth,
                year:nextyear
            };
        };
        this.render = function() {
            // Month/Year header
            var monthlabel = this.monthlabels[this.month];
            var outer = jQuery(document.createElement('div')).attr({
                'class':'pickerouter'
            });
            var table = jQuery(document.createElement('table')).attr({
                'class':'pickertable'
            });
            jQuery(outer).append(table);

            var row = jQuery(document.createElement('tr'));
            jQuery(table).append(row);
            var col = jQuery(document.createElement('th'));
            var link = jQuery(document.createElement('a')).attr({
                'href':'#',
                'rel':this.prevyear+''+this.prevmonth,
                'class':'navlink prev'
            }).html('<');
            jQuery(link).on('click', this.openPrevHandler);
            jQuery(col).append(link);
            jQuery(row).append(col);
            col = jQuery(document.createElement('th')).attr('colspan', 5).html(monthlabel + "&nbsp;" + this.year);
            jQuery(row).append(col);
            col = jQuery(document.createElement('th'));
            link = jQuery(document.createElement('a')).attr({
                'href':'#',
                'rel':this.nextyear+''+this.nextmonth,
                'class':'navlink next'
            }).html('>');
            jQuery(link).on('click', this.openNextHandler);
            jQuery(col).append(link);
            jQuery(row).append(col);

            // Weekday header
            row = jQuery(document.createElement('tr')).attr({
                'class':'calendar-header'
            });
            jQuery(table).append(row);
            for(var i = 0; i <= 6; i++ ){
                col = jQuery(document.createElement('td')).attr({
                    'class':'header weekday'
                }).html(this.daylabels[i]);
                jQuery(row).append(col);
            }

            // Days
            var day = 1;
            var day2 = 1;
            row = jQuery(document.createElement('tr'));
            for(var i = 0; i < 9; i++) {
                for(var d = 1; d <= 7; d++) {
                    col = document.createElement('td');
                    var dayouter = jQuery(document.createElement('div')).attr('class', 'dayouter');
                    jQuery(col).append(dayouter);
                    if(i==0 && d<this.startday) {
                        jQuery(col).attr('class', 'day prevmonth inactive');
                        var prevday = this.prevmonthlength - this.startday + this.dayoffset + d;
                        var dateid = this.getDateId(this.prevyear, this.prevmonth, prevday);
                        var customdata = (this.dayscustom[dateid]!=null)? this.dayscustom[dateid] : {};
                        var label = this.renderLabel(customdata);
                        var link = jQuery(document.createElement('a')).attr({
                            'href':'#',
                            'rel':dateid,
                            'class':'daylink'
                        }).html('<span class="dayinner">'+prevday+'</span>'+label);
                        jQuery(link).on('click', this.nothingHandler);
                        jQuery(dayouter).append(link);
                    } else if(day <= this.monthlength && (i > 0 || d >= this.startday)) {
                        var dateid = this.getDateId(this.year, this.month+1, day);
                        var customdata = (this.dayscustom[dateid]!=null)? this.dayscustom[dateid] : {};
                        var label = this.renderLabel(customdata);
                        var highlight = (this.daysselected.indexOf(dateid)!=-1)? ' highlight':'';
                        var inactive = (this.daysinactive.indexOf(dateid)!=-1 || (customdata.class!=null && customdata.class.indexOf('inactive')!=-1))? ' inactive':'';
                        if(this.inactivepast) {
                            if(this.year<this.currentdate.getFullYear() || (this.year==this.currentdate.getFullYear() && this.month<this.currentdate.getMonth()) || (this.month==this.currentdate.getMonth() && day<this.currentdate.getDate())) inactive = ' inactive';
                        }
                        var today = (this.month==this.currentdate.getMonth() && day==this.currentdate.getDate())? ' today':'';
                        jQuery(col).attr('class', 'day'+highlight+inactive+today);
                        var link = jQuery(document.createElement('a')).attr({
                            'href':'#',
                            'rel':dateid,
                            'class':'daylink'
                        }).html('<span class="dayinner">'+day+'</span>'+label);
                        if(inactive=='') {
                            jQuery(link).on('click', this.selectDayHandler);
                        } else {
                            jQuery(link).on('click', this.nothingHandler);
                        }
                        jQuery(dayouter).append(link);
                        day++;
                    } else {
                        var dateid = this.getDateId(this.nextyear, this.nextmonth+1, day2);
                        var customdata = (this.dayscustom[dateid]!=null)? this.dayscustom[dateid] : {};
                        var label = this.renderLabel(customdata);
                        jQuery(col).attr('class', 'day nextmonth inactive');
                        var link = jQuery(document.createElement('a')).attr({
                            'href':'#',
                            'rel':dateid,
                            'class':'daylink'
                        }).html('<span class="dayinner">'+day2+'</span>'+label);
                        jQuery(link).on('click', this.nothingHandler);
                        jQuery(dayouter).append(link);
                        day2++;
                    }
                    if(customdata!=null && customdata.class!=null) {
                        jQuery(col).addClass(customdata.class);
                    }
                    jQuery(row).append(col);
                }
                // stop making rows if we've run out of days
                if (day > this.monthlength) {
                    break;
                } else {
                    jQuery(table).append(row);
                    row = jQuery(document.createElement('tr'));
                }
            }
            jQuery(table).append(row);

            if(this.popup) {
                var row = jQuery(document.createElement('tr'));
                jQuery(table).append(row);
                var col = jQuery(document.createElement('th'));
                jQuery(row).append(col);
                col = jQuery(document.createElement('th')).attr('colspan', 5);
                var link = jQuery(document.createElement('a')).attr({
                    'href':'#',
                    'class':'closelink'
                }).html('Done');
                jQuery(link).on('click', this.closeHandler);
                jQuery(col).append(link);
                jQuery(row).append(col);
                col = jQuery(document.createElement('th'));
                jQuery(row).append(col);
            }

            jQuery(this.container).append(outer);
        };
        this.renderLabel = function(data) {
            if(data.label==null) return '';
            var html = '<span class="daylabel">'+data.label+'</span>';
            return html;
        };
        this.renderData = function() {
            var daylinks = jQuery(this.container).find('a.daylink');
            jQuery.each(daylinks, function(index, link) {
                var col = jQuery(link).closest('td');
                var dateid = jQuery(link).attr('rel');
                var date = self.stringToDate(dateid);
                date.month = date.month-1;

                var customdata = (self.dayscustom[dateid]!=null)? self.dayscustom[dateid] : {};
                var inactive = false;
                if(date.month<self.month) {
                    // Prev month
                    var label = self.renderLabel(customdata);
                    jQuery(col).attr('class', 'day prevmonth inactive');
                    jQuery(link).html('<span class="dayinner">'+date.day+'</span>'+label);
                    inactive = true;
                } else if(date.month==self.month) {
                    // Current month
                    inactive = (self.daysinactive.indexOf(dateid)!=-1 || (customdata.class!=null && customdata.class.indexOf('inactive')!=-1));
                    if(self.inactivepast) {
                        if(date.year<self.currentdate.getFullYear() || (date.year==self.currentdate.getFullYear() && date.month<self.currentdate.getMonth()) || (date.month==self.currentdate.getMonth() && date.day<self.currentdate.getDate())) inactive = true;
                    }
                    var label = self.renderLabel(customdata);
                    var highlight = (self.daysselected.indexOf(dateid)!=-1)? ' highlight':'';
                    if(inactive) highlight+= ' inactive';

                    var today = (self.month==self.currentdate.getMonth() && date.day==self.currentdate.getDate())? ' today':'';
                    jQuery(col).attr('class', 'day'+highlight+today);
                    jQuery(link).html('<span class="dayinner">'+date.day+'</span>'+label);
                } else {
                    // Next month
                    var label = self.renderLabel(customdata);
                    jQuery(col).attr('class', 'day nextmonth inactive');
                    jQuery(link).html('<span class="dayinner">'+date.day+'</span>'+label);
                    inactive = true;
                }
                if(customdata!=null && customdata.class!=null) {
                    jQuery(col).addClass(customdata.class);
                }
                if(!inactive) {
                    jQuery(link).on('click', self.selectDayHandler);
                } else {
                    jQuery(link).off('click', self.selectDayHandler);
                    jQuery(link).on('click', self.nothingHandler);
                }
            });
        };
        this.selectDay = function(e) {
            var target = e.target != null ? e.target : e.srcElement;
            var link = (jQuery(target).is('a'))? target : jQuery(target).closest('a');
            var date = jQuery(link).attr('rel');
            var col = jQuery(link).closest('td');

            if(!this.rangepicker) {
                if(jQuery(col).hasClass('highlight')) {
                    jQuery(col).removeClass('highlight');
                    this.daysselected.pop(date);
                } else {
                    jQuery(col).addClass('highlight');
                    this.daysselected.push(date);
                }
            }
            // Counter intuitive
            /*if(this.totalselectable>0 && this.daysselected.length>this.totalselectable) {
             var rel = this.daysselected[0];
             jQuery(this.container).find('[rel='+rel+']').closest('td').removeClass('highlight');
             this.daysselected.splice(0,1);
             }*/

            if(jQuery(this.element).is('input')) {
                jQuery(this.element).val(date);
            }
            if(this.rangepicker && date!=null) {
                jQuery(col).addClass('highlight rangehighlight');
                if(this.rangemode=='from') {
                    this.rangefrom = date;
                } else if(this.rangemode=='to') {
                    this.rangeto = date;
                } else if(this.rangemode=='both') {
                    if(this.rangepart=='from') {
                        this.rangefrom = date;
                        var dateval = parseInt(date.replace(/-/g, ''));
                        var result = this.stringToDate(dateval+this.rangesize);
                        this.rangeto = this.getDateId(result.year, result.month, result.day);
                        this.rangepart = 'to';
                    } else {
                        this.rangeto = date;
                        this.rangepart = 'from';
                    }
                    if(this.rangefrom>this.rangeto) {
                        var tmp = this.rangefrom;
                        this.rangefrom = this.rangeto;
                        this.rangeto = tmp;
                    }
                    this.setRangeFrom(this.rangefrom);
                    this.setRangeTo(this.rangeto);
                }
                this.updateRange();
            }
            jQuery(this.element).trigger('didSelectDay', date);
            if(this.shouldAutoClose()) this.close();
        };
        this.updateRange = function() {
            this.daysselected = [];
            if(this.rangefrom!=null && this.rangeto!=null) {
                jQuery(this.container).find('.highlight,.rangehighlight').removeClass('highlight rangehighlight');
                var rangedate = parseInt(this.rangefrom.replace(/-/g, ''));

                var rangeto = parseInt(this.rangeto.replace(/-/g, ''));
                var year = parseInt(rangedate.toString().substr(0,4));
                var month = parseInt(rangedate.toString().substr(4,2));
                var day = parseInt(rangedate.toString().substr(6,2));
                var i = day;
                var hasbroken = false;
                var prevdate = this.rangefrom;
                while(rangedate<=rangeto) {
                    var dateid = this.getDateId(year, month, i);
                    var col = jQuery(this.container).find('[rel='+dateid+']').closest('td');
                    this.daysselected.push(dateid);
                    jQuery(col).addClass('highlight rangehighlight');

                    if(this.daysinactive.indexOf(dateid)!=-1) {
                        this.setRangeTo(dateid);
                        hasbroken = true;
                        break;
                    }
                    i++;
                    var monthlength = this.getMonthLength(month-1, year);
                    if(i>monthlength) {
                        var result = this.getNextMonth(month-1, year);
                        month = result.month+1;
                        year = result.year;
                        i = 1;
                    }
                    rangedate = parseInt(this.pad(year,4)+''+this.pad(month,2)+''+this.pad(i,2));
                    prevdate = dateid;
                }
                //if(hasbroken) this.updateRange();
                this.renderData();
                jQuery(this.element).trigger('didUpdateRange', this.rangefrom, this.rangeto);
            }
        };
        this.setRangeFrom = function(date) {
            var result = true;
            if(this.rangeto!=null) {
                var rangefrom = parseInt(date.replace(/-/g, ''));
                var rangeto = parseInt(this.rangeto.replace(/-/g, ''));
                if(rangefrom>rangeto) {
                    this.rangeto = date;
                    if(this.rangemode=='to' && jQuery(this.element).is('input')) jQuery(this.element).val(date);
                    result = false;
                }
            } else {
                this.rangeto = date;
                result = false;
            }
            this.rangefrom = date;
            jQuery(this.element).trigger('didSetRangeFrom', this.rangefrom);
            return result;
        };
        this.setRangeTo = function(date) {
            var date = date.toString();
            var result = true;
            if(this.rangefrom!=null) {
                var rangefrom = parseInt(this.rangefrom.replace(/-/g, ''));
                var rangeto = parseInt(date.replace(/-/g, ''));
                if(rangefrom>rangeto) {
                    var tmp = rangefrom;
                    rangefrom = rangeto;
                    rangeto = tmp;
                }
                if(rangefrom>rangeto) {
                    this.rangefrom = date;
                    if(this.rangemode=='from' && jQuery(this.element).is('input')) jQuery(this.element).val(date);
                    result = false;
                }
            } else {
                this.rangefrom = date;
                result = false;
            }
            this.rangeto = date;
            jQuery(this.element).trigger('didSetRangeTo', this.rangeto);
            return result;
        };
        this.getDateId = function(year, month, day) {
            var result = this.pad(year, 4)+'-'+this.pad(month, 2)+'-'+this.pad(day, 2);
            return result;
        };
        this.pad = function(num, size) {
            var s = num+"";
            while (s.length < size) s = "0" + s;
            return s;
        }
        this.shouldAutoClose = function() {
            if(this.popup && this.totalselectable==1) {
                return true;
            } else {
                return false;
            }
        };
        this.close = function() {
            jQuery(this.container).css('display', 'none');
        };

        this.cancel = function(e) {
            if(this.container!=null) {
                var target = (e && e.target) || (event && event.srcElement);
                // Close if outside element, otherwise run actions
                if(this.checkparent(target)) {
                    this.close();
                }
            }
        };
        this.checkparent = function(t) {
            // Test if click is inside or outside container
            while(t.parentNode){
                if(t == jQuery(this.container).get(0)){
                    return false;
                }
                t=t.parentNode
            }
            return true;
        };
        this.valueChanged = function(e) {
            var value = jQuery(this.element).val();
            if(value!=null && value.length>=4) {
                var result = this.stringToDate(value);
                var year = result.year;
                var month = result.month;
                var day = result.day;

                var dateid = this.getDateId(year, month, day);
                if(this.rangepicker) {
                    if(this.rangemode=='from') {
                        this.setRangeFrom(dateid);
                    } else if(this.rangemode=='to') {
                        this.setRangeTo(dateid);
                    }
                }
                jQuery(this.element).trigger('didChangeValue', dateid);
                this.rebuild(year, month-1);
            }
        };
        this.stringToDate = function(value) {
            value = value.toString();
            var year = this.currentdate.getFullYear();
            var month = this.currentdate.getMonth();
            var day = this.currentdate.getDate();
            if(value.indexOf('-')!=-1) {
                // yyyy-mm-dd
                var dateparts = value.split('-',3);
                year = dateparts[0];
                if(year.length==2) year = this.currentdate.getFullYear().toString().substr(0,2)+year;
                month = (dateparts.length>1)? dateparts[1] : '01';
                day = (dateparts.length>2)? dateparts[2] : '01';
                // yyyy-dd-mm fallback
                if(month>12) {
                    var tmp = month;
                    month = day;
                    day = tmp;
                }
            } else if(value.indexOf('/')!=-1) {
                // dd/mm/yyyy
                var dateparts = value.split('/',3);
                day = dateparts[0];
                month = (dateparts.length>1)? dateparts[1] : '01';
                year = (dateparts.length>2)? dateparts[2] : this.currentdate.getFullYear();
                if(year.length==2) year = this.currentdate.getFullYear().toString().substr(0,2)+year;
                // mm/dd/yyyy fallback
                if(month>12) {
                    var tmp = month;
                    month = day;
                    day = tmp;
                }
            } else {
                // yyyymmdd
                year = value.substr(0,4);
                month = value.substr(4,2);
                day = value.substr(6,2);
            }
            return {
                year:parseInt(year),
                month:parseInt(month),
                day:parseInt(day)
            };
        };

        // Setup Handlers
        this.clickHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            self.open();
        };
        this.focusHandler = function(e) {
            self.open();
        };
        this.valueChangedHandler = function(e) {
            self.valueChanged(e);
        };
        this.cancelClickHandler = function(e) {
            self.cancel(e);
        };
        this.openPrevHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            self.openPrev();
        };
        this.openNextHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            self.openNext();
        };
        this.closeHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            self.close();
        };
        this.selectDayHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
            self.selectDay(e);
        };
        this.nothingHandler = function(e) {
            e.preventDefault();
            e.stopPropagation();
        };

        // Init
        this.init = function() {
            // Stop default browser auto completers
            jQuery(this.element).attr("autocomplete", "off");
            if(this.popup) {
                jQuery(this.element).on({
                    'click':this.clickHandler,
                    'focus':this.focusHandler,
                    'change':this.valueChangedHandler
                });
                jQuery(document).on('click', this.cancelClickHandler);
            } else {
                jQuery(this.element).on({
                    'change':this.valueChangedHandler
                });
                this.open();
            }
        };
        this.init();
    };
    jQuery.fn.jidatepicker = function(options) {
        var element = jQuery(this);
        if(element.data('jidatepicker')) return element.data('jidatepicker');
        var jidatepicker = new JiDatePicker(this, options);
        element.data('jidatepicker', jidatepicker);
        return jidatepicker;
    };
})(jQuery);
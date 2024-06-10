@if (isset($limit))
    @limit($user, $limit)
        User has enough limit to create locations
    @else
        User does not have enough limit to create locations
    @endlimit
@else
    @limit($user, 'locations', 'standard')
        User has enough limit to create locations
    @else
        User does not have enough limit to create locations
    @endlimit
@endif
